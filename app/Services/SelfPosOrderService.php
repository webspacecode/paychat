<?php

namespace App\Services;

use App\Events\KitchenBatchCreated;
use App\Http\Resources\Tenant\OrderResource;
use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\TableSession;
use App\Services\KitchenBatchService;
use App\Services\Payments\PaymentService;
use App\Support\IndustryNormalizer;
use App\Support\Observability;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Generator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Throwable;

class SelfPosOrderService
{
    public function __construct(
        private PaymentService $payments,
        private TokenService $tokens,
        private OrderKitchenDispatchService $dispatch,
        private KitchenBatchService $kitchenBatches,
        private InvoiceService $invoices,
        private TableSessionService $tableSessions,
    ) {
    }

    public function submit(Order $order, array $payload): array
    {
        $method = strtolower((string) ($payload['payment_method'] ?? ''));
        if (! in_array($method, ['cash', 'upi'], true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Self POS supports cash and UPI only.',
            ]);
        }

        $order = DB::transaction(function () use ($order, $payload, $method) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $this->assertSubmittable($locked);
            $this->applyCustomerSnapshot($locked, $payload['customer'] ?? []);

            $meta = $locked->meta ?? [];
            $meta['source'] = 'self_pos';
            $meta['self_pos'] = array_merge($meta['self_pos'] ?? [], [
                'submitted' => true,
                'submitted_at' => $meta['self_pos']['submitted_at'] ?? now()->toISOString(),
                'payment_method' => $method,
                'requires_biller_confirmation' => true,
            ]);

            $updates = [
                'status' => 'pending_payment',
                'payment_status' => $locked->payment_status ?: 'unpaid',
                'balance_due' => max(0, round((float) $locked->total - (float) $locked->paid_amount, 2)),
                'meta' => $meta,
            ];

            if (Schema::hasColumn('pos_orders', 'source')) {
                $updates['source'] = 'self_pos';
            }

            $locked->update($updates);

            return $locked->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);
        });

        $order = $this->ensureTableSessionForSelfPos($order);
        $payment = $this->ensurePendingPayment($order, $method, $payload);
        $this->attachPaymentSnapshot($order, $payment);
        $kitchen = $this->ensureKitchenSideEffects($order->fresh(['items.product', 'token', 'tableSession', 'kitchenBatches.items.product']));
        $freshOrder = $order->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);
        $kitchenQr = $this->kitchenQrFor($freshOrder->token);

        return [
            'success' => true,
            'message' => 'Order sent to kitchen. Payment confirmation is pending with biller.',
            'order' => (new OrderResource($freshOrder))->resolve(request()),
            'payment' => $payment?->fresh('upiProfile'),
            'token' => $freshOrder->token,
            'kitchen_qr' => $kitchenQr,
            'kitchenQr' => $kitchenQr,
            'kitchen' => $kitchen,
            'requires_biller_confirmation' => true,
            'payment_display_status' => $method === 'upi' ? 'processed_pending_verification' : 'cash_pending_collection',
            'invoice_generated' => false,
        ];
    }

    public function confirmPayment(Order $order, ?string $method = null): array
    {
        $order = $order->fresh(['payments', 'items.product', 'token']);
        $this->assertConfirmable($order);

        $payment = $this->pendingPaymentForConfirmation($order, $method);

        if (! $payment) {
            $payment = $this->ensurePendingPayment($order, $method ?: $this->selfPosMethod($order) ?: 'cash', []);
        }

        $result = $this->payments->markPaymentSuccess($payment);

        $freshOrder = $result['order']->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);
        $invoice = $this->generateFinalInvoice($freshOrder);
        $this->closeTableSessionIfNeeded($freshOrder);
        $freshOrder = $freshOrder->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);

        $meta = $freshOrder->meta ?? [];
        $meta['self_pos'] = array_merge($meta['self_pos'] ?? [], [
            'requires_biller_confirmation' => false,
            'confirmed_at' => now()->toISOString(),
            'confirmed_by' => auth()->id(),
        ]);
        $freshOrder->update(['meta' => $meta]);
        $freshOrder = $freshOrder->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);

        return [
            'success' => true,
            'message' => 'Self POS payment confirmed.',
            'payment_status' => 'success',
            'payment' => $payment->fresh(),
            'order' => (new OrderResource($freshOrder))->resolve(request()),
            'token' => $freshOrder->token,
            'invoice' => $invoice,
            'invoice_generated' => (bool) ($invoice['invoice_id'] ?? $invoice['invoice_no'] ?? null),
        ];
    }

    private function assertSubmittable(Order $order): void
    {
        if (in_array($order->status, ['cancelled', 'completed'], true)) {
            throw new ConflictHttpException('This order can no longer be submitted.');
        }

        if ($order->items()->where('quantity', '>', 0)->count() === 0) {
            throw ValidationException::withMessages([
                'items' => 'Add at least one item before placing the order.',
            ]);
        }
    }

    private function assertConfirmable(Order $order): void
    {
        if (in_array($order->status, ['cancelled', 'completed'], true)) {
            throw new ConflictHttpException('This order can no longer be confirmed.');
        }

        if ($order->payment_status === 'paid') {
            throw new ConflictHttpException('This order is already paid.');
        }
    }

    private function applyCustomerSnapshot(Order $order, array $customer): void
    {
        $updates = [];
        if (array_key_exists('name', $customer)) {
            $updates['customer_name'] = $customer['name'] ?: null;
        }
        if (array_key_exists('phone', $customer)) {
            $updates['customer_phone'] = $customer['phone'] ?: null;
        }
        if (array_key_exists('email', $customer) && Schema::hasColumn('pos_orders', 'customer_email')) {
            $updates['customer_email'] = $customer['email'] ?: null;
        }

        if ($updates) {
            $order->update($updates);
        }
    }

    private function ensurePendingPayment(Order $order, string $method, array $payload): ?Payment
    {
        $amount = $this->payableAmount($order);
        $payloadPayment = $this->pendingPaymentFromPayload($order, $method, $amount, $payload);

        if ($payloadPayment) {
            return $payloadPayment;
        }

        $existing = $order->payments()
            ->where('payment_method', $method)
            ->where('status', 'pending')
            ->where('amount', $amount)
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        if ($method === 'upi') {
            $created = $this->payments->createPayment(
                $order->fresh(),
                'upi',
                $amount,
                isset($payload['upi_profile_id']) ? (int) $payload['upi_profile_id'] : null
            );

            return $created instanceof Payment ? $created : ($created['payment'] ?? null);
        }

        return Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => $amount,
            'status' => 'pending',
            'meta' => [
                'source' => 'self_pos',
                'requires_biller_confirmation' => true,
            ],
        ]);
    }

    private function ensureTableSessionForSelfPos(Order $order): Order
    {
        $fresh = $order->fresh(['tableSession']);

        if (! $this->isTableService($fresh) || ! $fresh->table_id || in_array($fresh->status, ['completed', 'cancelled'], true)) {
            return $fresh;
        }

        if ($fresh->tableSession && $fresh->tableSession->status === 'active') {
            if (strtolower((string) $fresh->order_type) !== 'dine_in') {
                $fresh->update(['order_type' => 'dine_in']);
            }

            return $fresh->fresh(['tableSession']);
        }

        $activeForOrder = TableSession::query()
            ->where('order_id', $fresh->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($activeForOrder) {
            $fresh->update([
                'table_id' => $activeForOrder->table_id,
                'table_session_id' => $activeForOrder->id,
                'guest_count' => $fresh->guest_count ?: $activeForOrder->guest_count,
                'dining_flow' => 'table_service',
                'order_type' => 'dine_in',
            ]);

            return $fresh->fresh(['tableSession']);
        }

        if (strtolower((string) $fresh->order_type) !== 'dine_in') {
            $fresh->update(['order_type' => 'dine_in']);
            $fresh = $fresh->fresh(['tableSession']);
        }

        try {
            $this->tableSessions->assignOrder(
                $fresh,
                (int) $fresh->table_id,
                $fresh->guest_count ?: 1
            );

            return $fresh->fresh(['tableSession']);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first()
                ?: 'This table is already active. Please contact staff.';

            throw ValidationException::withMessages([
                'table_id' => $message,
            ]);
        }
    }

    private function pendingPaymentFromPayload(Order $order, string $method, float $amount, array $payload): ?Payment
    {
        if (empty($payload['payment_id'])) {
            return null;
        }

        $payment = Payment::whereKey((int) $payload['payment_id'])
            ->where('order_id', $order->id)
            ->first();

        if (! $payment) {
            throw ValidationException::withMessages([
                'payment_id' => 'Selected payment attempt does not belong to this order.',
            ]);
        }

        if ($payment->payment_method !== $method || $payment->status !== 'pending') {
            throw ValidationException::withMessages([
                'payment_id' => 'Selected payment attempt is not available for confirmation.',
            ]);
        }

        if (round((float) $payment->amount, 2) !== round($amount, 2)) {
            throw ValidationException::withMessages([
                'payment_id' => 'Selected payment attempt amount does not match the order amount.',
            ]);
        }

        return $payment;
    }

    private function attachPaymentSnapshot(Order $order, ?Payment $payment): void
    {
        if (! $payment) {
            return;
        }

        $fresh = $order->fresh();
        $meta = $fresh->meta ?? [];
        $meta['self_pos'] = array_merge($meta['self_pos'] ?? [], [
            'payment_id' => $payment->id,
            'payment_status' => $payment->status,
            'customer_submitted_after_upi' => $payment->payment_method === 'upi',
        ]);

        $fresh->update(['meta' => $meta]);
    }

    private function payableAmount(Order $order): float
    {
        $fresh = $order->fresh();
        $balanceDue = round((float) ($fresh->balance_due ?? 0), 2);
        $total = round((float) ($fresh->total ?? 0), 2);

        if ($balanceDue > 0) {
            return $balanceDue;
        }

        return $total;
    }

    private function ensureKitchenSideEffects(Order $order): array
    {
        if ($this->isTableService($order)) {
            $token = $this->tokens->generateForSelfPosSubmission($order);
            $batch = null;

            try {
                $batch = $this->kitchenBatches->sendFreshItems($order, KitchenBatchService::CHANNEL_BOARD);
                if ($this->kitchenBatches->shouldBroadcastToKds($batch)) {
                    event(new KitchenBatchCreated($batch));
                }
            } catch (Throwable $e) {
                Observability::logFailure('self_pos.table_kitchen_dispatch.failed', $e, [
                    'order_id' => $order->id,
                    'location_id' => $order->location_id,
                    'table_id' => $order->table_id,
                    'table_session_id' => $order->table_session_id,
                    'action' => 'self_pos.submit',
                ]);
            }

            return [
                'mode' => 'table_service',
                'token' => $token,
                'batch' => $batch,
                'status' => $batch ? 'sent' : ($token ? 'token_created' : 'pending'),
            ];
        }

        $token = $this->dispatch->ensureTokenAndDispatchWhenReady($order, 'self_pos_submitted');

        return [
            'mode' => 'qsr',
            'token' => $token,
            'status' => $token ? 'sent' : 'pending',
        ];
    }

    private function kitchenQrFor($token): ?string
    {
        $tokenCode = data_get($token, 'token_code');
        if (! $tokenCode) {
            return null;
        }

        try {
            return base64_encode((new Generator())->format('svg')->size(180)->generate(
                url('pos#/kitchen?mode=staff&token='.rawurlencode($tokenCode))
            ));
        } catch (Throwable $e) {
            Observability::logWarning('self_pos.kitchen_qr_generation.failed', $e, [
                'token_code' => $tokenCode,
            ]);

            return null;
        }
    }

    private function pendingPaymentForConfirmation(Order $order, ?string $method): ?Payment
    {
        return $order->payments()
            ->where('status', 'pending')
            ->when($method, fn ($query) => $query->where('payment_method', strtolower($method)))
            ->latest('id')
            ->first();
    }

    private function selfPosMethod(Order $order): ?string
    {
        return data_get($order->meta, 'self_pos.payment_method');
    }

    private function isTableService(Order $order): bool
    {
        return strtolower(trim((string) $order->dining_flow)) === 'table_service';
    }

    private function generateFinalInvoice(Order $order): array
    {
        $order = $order->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);

        $invoice = [
            'invoice_id' => $order->invoice_id,
            'invoice_no' => $order->invoice_no,
            'url' => data_get($order->meta, 'invoice.url'),
        ];

        if ($order->invoice_id || $order->invoice_no || $order->payment_status !== 'paid' || $order->status !== 'completed') {
            return $invoice;
        }

        try {
            $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
            if (! $tenant) {
                return $invoice;
            }

            $industry = IndustryNormalizer::normalize((string) $tenant->industry);
            $templates = config("invoice.industries.{$industry}.templates", []);
            $paper = array_key_exists('80mm', $templates)
                ? '80mm'
                : ((string) array_key_first($templates) ?: 'a4');

            $payload = (new OrderResource($order))->resolve(request());
            $result = $this->invoices->generate($payload, $tenant, $tenant->industry, $paper);
            $fresh = $order->fresh();

            return [
                'invoice_id' => $fresh->invoice_id,
                'invoice_no' => $fresh->invoice_no,
                'url' => $result['url'] ?? data_get($fresh->meta, 'invoice.url'),
            ];
        } catch (Throwable $e) {
            Observability::logFailure('self_pos.invoice_generation.failed', $e, [
                'order_id' => $order->id,
                'location_id' => $order->location_id,
                'action' => 'self_pos.confirm_payment',
            ]);

            return $invoice;
        }
    }

    private function closeTableSessionIfNeeded(Order $order): void
    {
        if (! $this->isTableService($order) || $order->payment_status !== 'paid') {
            return;
        }

        try {
            $this->tableSessions->closeForOrder($order);
        } catch (Throwable $e) {
            Observability::logFailure('self_pos.table_session_close.failed', $e, [
                'order_id' => $order->id,
                'table_session_id' => $order->table_session_id,
                'action' => 'self_pos.confirm_payment',
            ]);
        }
    }
}
