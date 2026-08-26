<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\PaymentMethodCorrection;
use App\Models\Tenant\UpiProfile;
use App\Models\User;
use App\Http\Resources\Tenant\OrderResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PaymentMethodCorrectionService
{
    private const SUPPORTED_METHODS = ['cash', 'upi', 'phonepe'];

    public function __construct(
        private readonly IdempotencyService $idempotency,
        private readonly ReportEngineService $reports
    ) {
    }

    public function correct(Order $order, array $payload, ?User $actor, ?string $idempotencyKey = null): array
    {
        $this->ensureSupportSchema();

        $requestPayload = [
            'order_id' => (int) $order->id,
            'payment_id' => $payload['payment_id'] ?? null,
            'new_method' => strtolower(trim((string) $payload['new_method'])),
            'upi_profile_id' => $payload['upi_profile_id'] ?? null,
            'reason' => trim((string) $payload['reason']),
        ];

        $idempotencyKey = $this->normalizeIdempotencyKey($idempotencyKey, $requestPayload);
        $idempotencyState = $this->idempotency->acquire(
            'payment_correction.order.' . $order->id,
            $idempotencyKey,
            $requestPayload
        );

        if (! ($idempotencyState['execute'] ?? false)) {
            return $idempotencyState['response'] ?? [];
        }

        try {
            $response = $this->applyCorrection($order, $requestPayload, $actor, $idempotencyKey);
            $correctionId = data_get($response, 'correction.id');

            $this->idempotency->complete(
                $idempotencyState['record'],
                200,
                $response,
                $correctionId ? 'payment_method_correction' : 'payment',
                $correctionId ?: data_get($response, 'payment.id')
            );

            return $response;
        } catch (\Throwable $e) {
            $this->idempotency->fail($idempotencyState['record']);
            throw $e;
        }
    }

    private function applyCorrection(Order $inputOrder, array $payload, ?User $actor, string $idempotencyKey): array
    {
        return DB::connection('tenant')->transaction(function () use ($inputOrder, $payload, $actor, $idempotencyKey) {
            $order = Order::whereKey($inputOrder->id)->lockForUpdate()->firstOrFail();

            $this->assertOrderCanBeCorrected($order);

            $payment = $this->resolvePayment($order, $payload['payment_id'] ?? null);
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $this->assertPaymentCanBeCorrected($order, $payment);

            $newMethod = $this->validateMethod($payload['new_method']);
            $newUpiProfile = $this->resolveUpiProfile($newMethod, $payload['upi_profile_id'] ?? null, $order);

            $oldMethod = strtolower((string) $payment->payment_method);
            $oldUpiProfileId = $payment->upi_profile_id;
            $oldQrUrl = $payment->upi_qr_url;
            $oldMeta = $payment->meta ?: [];
            $newUpiProfileId = $newUpiProfile?->id;
            $changed = $oldMethod !== $newMethod || (int) ($oldUpiProfileId ?? 0) !== (int) ($newUpiProfileId ?? 0);

            if (! $changed) {
                return $this->response(false, 'Payment method already matches the requested method.', $order, $payment, null, [
                    'invoice_snapshot' => 'not_required',
                    'reports' => 'not_required',
                ]);
            }

            $correction = PaymentMethodCorrection::create([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'old_payment_method' => $oldMethod,
                'new_payment_method' => $newMethod,
                'old_upi_profile_id' => $oldUpiProfileId,
                'new_upi_profile_id' => $newUpiProfileId,
                'amount' => $payment->amount,
                'reason' => $payload['reason'],
                'corrected_by' => $actor?->id,
                'corrected_at' => now(),
                'idempotency_key_hash' => hash('sha256', $idempotencyKey),
                'meta' => [
                    'old_upi_qr_url' => $oldQrUrl,
                    'old_meta' => $oldMeta,
                    'actor_role' => $actor?->role,
                ],
            ]);

            $paymentMeta = $oldMeta;
            $paymentMeta['payment_method_correction'] = [
                'latest_correction_id' => $correction->id,
                'old_payment_method' => $oldMethod,
                'new_payment_method' => $newMethod,
                'corrected_by' => $actor?->id,
                'corrected_at' => $correction->corrected_at?->toISOString(),
            ];

            $payment->forceFill([
                'payment_method' => $newMethod,
                'upi_profile_id' => $newMethod === 'upi' ? $newUpiProfileId : null,
                'upi_qr_url' => $newMethod === 'upi' ? $oldQrUrl : null,
                'meta' => $paymentMeta,
            ])->save();

            $invoiceStatus = $this->updateInvoiceSnapshot($order, $payment, $correction, $newUpiProfile);
            $reportStatus = $this->refreshReports($order);

            $order = $order->fresh(['items.product', 'customer', 'location', 'payments.upiProfile', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);
            $payment = $payment->fresh(['upiProfile']);
            $correction = $correction->fresh();

            return $this->response(true, 'Payment method corrected', $order, $payment, $correction, [
                'invoice_snapshot' => $invoiceStatus,
                'reports' => $reportStatus,
            ]);
        }, 3);
    }

    private function assertOrderCanBeCorrected(Order $order): void
    {
        if ($order->status !== 'completed' || $order->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'order' => 'Payment method can be corrected only for completed paid orders.',
            ]);
        }

        if (in_array($order->status, ['draft', 'cancelled', 'void', 'refunded'], true)) {
            throw ValidationException::withMessages([
                'order' => 'This order status does not allow payment correction.',
            ]);
        }
    }

    private function resolvePayment(Order $order, mixed $paymentId): Payment
    {
        $successfulPayments = Payment::where('order_id', $order->id)
            ->where('status', 'success')
            ->orderBy('id')
            ->get();

        if ($successfulPayments->isEmpty()) {
            throw ValidationException::withMessages([
                'payment_id' => 'No successful payment was found for this order.',
            ]);
        }

        if ($paymentId) {
            $payment = $successfulPayments->firstWhere('id', (int) $paymentId);

            if (! $payment) {
                throw ValidationException::withMessages([
                    'payment_id' => 'The selected payment does not belong to this order or is not successful.',
                ]);
            }

            return $payment;
        }

        if ($successfulPayments->count() > 1) {
            throw ValidationException::withMessages([
                'payment_id' => 'This order has multiple successful payments. Select the payment to correct.',
            ]);
        }

        return $successfulPayments->first();
    }

    private function assertPaymentCanBeCorrected(Order $order, Payment $payment): void
    {
        if ((int) $payment->order_id !== (int) $order->id || $payment->status !== 'success') {
            throw ValidationException::withMessages([
                'payment_id' => 'Only successful payments for this order can be corrected.',
            ]);
        }

        if ((float) $payment->amount <= 0) {
            throw new ConflictHttpException('Payment amount is invalid for correction.');
        }
    }

    private function validateMethod(string $method): string
    {
        $method = strtolower(trim($method));

        if (! in_array($method, self::SUPPORTED_METHODS, true)) {
            throw ValidationException::withMessages([
                'new_method' => 'This payment method is not supported for correction.',
            ]);
        }

        $enabled = PaymentMethod::where('type', $method)->where('enabled', true)->exists();

        if (! $enabled) {
            throw ValidationException::withMessages([
                'new_method' => 'This payment method is not enabled.',
            ]);
        }

        return $method;
    }

    private function resolveUpiProfile(string $method, mixed $upiProfileId, Order $order): ?UpiProfile
    {
        if ($method !== 'upi') {
            return null;
        }

        if (! $upiProfileId) {
            throw ValidationException::withMessages([
                'upi_profile_id' => 'Select an active UPI profile for UPI corrections.',
            ]);
        }

        $profile = UpiProfile::whereKey($upiProfileId)
            ->where('is_active', true)
            ->where(function ($query) use ($order) {
                $query->whereNull('location_id');

                if ($order->location_id) {
                    $query->orWhere('location_id', $order->location_id);
                }
            })
            ->first();

        if (! $profile) {
            throw ValidationException::withMessages([
                'upi_profile_id' => 'The selected UPI profile is not active for this order location.',
            ]);
        }

        return $profile;
    }

    private function updateInvoiceSnapshot(Order $order, Payment $payment, PaymentMethodCorrection $correction, ?UpiProfile $upiProfile): string
    {
        try {
            $tenantId = app()->bound('currentTenant') ? app('currentTenant')->id : null;

            if (! $tenantId) {
                return 'skipped';
            }

            $invoice = Invoice::query()
                ->where('tenant_id', $tenantId)
                ->where(function ($query) use ($order) {
                    $query->where('order_id', $order->id)
                        ->orWhere('uuid', $order->invoice_no);
                })
                ->orderByDesc('id')
                ->first();

            if (! $invoice) {
                return 'not_found';
            }

            $data = $invoice->order_data ?: [];
            $payments = Arr::get($data, 'payments', []);
            $updated = false;

            foreach ($payments as $index => $snapshotPayment) {
                if ((int) ($snapshotPayment['id'] ?? 0) === (int) $payment->id) {
                    $payments[$index] = $this->correctSnapshotPayment($snapshotPayment, $payment, $correction, $upiProfile);
                    $updated = true;
                    break;
                }
            }

            if (! $updated && count($payments) === 1) {
                $payments[0] = $this->correctSnapshotPayment($payments[0], $payment, $correction, $upiProfile);
                $updated = true;
            }

            if (! $updated) {
                return 'payment_not_found';
            }

            Arr::set($data, 'payments', $payments);
            Arr::set($data, 'meta.payment_method_correction.latest_id', $correction->id);
            Arr::set($data, 'meta.payment_method_correction.corrected_at', $correction->corrected_at?->toISOString());

            $invoice->forceFill(['order_data' => $data])->save();

            return 'updated';
        } catch (\Throwable $e) {
            Log::warning('payment_method_correction.invoice_snapshot_failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'correction_id' => $correction->id,
                'error' => $e->getMessage(),
            ]);

            return 'failed';
        }
    }

    private function correctSnapshotPayment(array $snapshotPayment, Payment $payment, PaymentMethodCorrection $correction, ?UpiProfile $upiProfile): array
    {
        $snapshotPayment['payment_method'] = $payment->payment_method;
        $snapshotPayment['upi_profile_id'] = $payment->upi_profile_id;
        $snapshotPayment['upi_qr_url'] = $payment->upi_qr_url;
        $snapshotPayment['upi_profile'] = $upiProfile ? [
            'id' => $upiProfile->id,
            'label' => $upiProfile->label,
            'payee_name' => $upiProfile->payee_name,
            'location_id' => $upiProfile->location_id,
        ] : null;
        $snapshotPayment['meta']['payment_method_correction'] = [
            'latest_correction_id' => $correction->id,
            'old_payment_method' => $correction->old_payment_method,
            'new_payment_method' => $correction->new_payment_method,
            'corrected_at' => $correction->corrected_at?->toISOString(),
        ];

        return $snapshotPayment;
    }

    private function refreshReports(Order $order): string
    {
        try {
            $tenantId = app()->bound('currentTenant') ? app('currentTenant')->id : null;

            if (! $tenantId) {
                return 'skipped';
            }

            $date = $order->business_date ?: Carbon::parse($order->created_at)->toDateString();
            $this->reports->generateDailyReports($tenantId, $date);

            return 'refreshed';
        } catch (\Throwable $e) {
            Log::warning('payment_method_correction.report_refresh_failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return 'failed';
        }
    }

    private function response(bool $changed, string $message, Order $order, Payment $payment, ?PaymentMethodCorrection $correction, array $sideEffects): array
    {
        return [
            'success' => true,
            'changed' => $changed,
            'message' => $message,
            'order' => (new OrderResource($order))->resolve(),
            'payment' => $payment->toArray(),
            'correction' => $correction?->toArray(),
            'side_effects' => $sideEffects,
        ];
    }

    private function normalizeIdempotencyKey(?string $key, array $payload): string
    {
        $key = trim((string) $key);

        if ($key !== '') {
            return $key;
        }

        return 'payment-correction-' . hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function ensureSupportSchema(): void
    {
        if (! Schema::connection('tenant')->hasTable('idempotency_requests')) {
            Schema::connection('tenant')->create('idempotency_requests', function (Blueprint $table) {
                $table->id();
                $table->string('scope', 100);
                $table->char('idempotency_key_hash', 64);
                $table->char('request_hash', 64);
                $table->string('status', 20)->default('processing');
                $table->unsignedSmallInteger('response_code')->nullable();
                $table->text('response_body')->nullable();
                $table->string('resource_type', 100)->nullable();
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->dateTime('locked_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->dateTime('expires_at')->nullable()->index();
                $table->timestamps();
                $table->unique(['scope', 'idempotency_key_hash'], 'idempotency_scope_key_unique');
                $table->index(['status', 'expires_at']);
            });
        }

        if (! Schema::connection('tenant')->hasTable('payment_method_corrections')) {
            Schema::connection('tenant')->create('payment_method_corrections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('payment_id');
                $table->string('old_payment_method', 50);
                $table->string('new_payment_method', 50);
                $table->unsignedBigInteger('old_upi_profile_id')->nullable();
                $table->unsignedBigInteger('new_upi_profile_id')->nullable();
                $table->decimal('amount', 12, 2);
                $table->text('reason');
                $table->unsignedBigInteger('corrected_by')->nullable();
                $table->timestamp('corrected_at')->nullable();
                $table->char('idempotency_key_hash', 64)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index('order_id');
                $table->index('payment_id');
                $table->index('corrected_by');
                $table->index('corrected_at');
                $table->index('old_payment_method');
                $table->index('new_payment_method');
                $table->index('idempotency_key_hash');
            });
        }
    }
}
