<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\UpiProfile;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Payments\PaymentService;
use App\Services\InvoiceService;
use App\Services\OrderKitchenDispatchService;
use App\Services\TableSessionService;
use App\Http\Resources\Tenant\OrderResource;
use App\Support\IndustryNormalizer;
use App\Support\Observability;
use App\Http\Requests\Tenant\InitiatePaymentRequest;
use App\Http\Controllers\Controller;
use Throwable;


class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function list($tenantSlug, Request $request)
    {
        $locationId = $request->input('location_id');
        $includeGlobal = $request->boolean('include_global', true);

        $upiProfiles = UpiProfile::query()
            ->where('is_active', true)
            ->when($locationId !== null, function ($query) use ($locationId, $includeGlobal) {
                $query->where(function ($q) use ($locationId, $includeGlobal) {
                    $q->where('location_id', $locationId);

                    if ($includeGlobal) {
                        $q->orWhereNull('location_id');
                    }
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get()
            ->map(fn (UpiProfile $profile) => [
                'id' => $profile->id,
                'label' => $profile->label,
                'upi_id_masked' => $this->maskUpiId($profile->upi_id),
                'payee_name' => $profile->payee_name,
                'location_id' => $profile->location_id,
                'is_default' => $profile->is_default,
                'is_active' => $profile->is_active,
            ])
            ->values();

        $methods = PaymentMethod::where('enabled', true)
            ->get()
            ->map(function ($m) use ($upiProfiles) {
                return [
                    'type' => $m->type,          // upi / cash / gateway
                    'mode' => $m->mode,          // personal / business / gateway
                    'display_name' => strtoupper($m->type), // UI label
                    'provider' => $m->config['provider'] ?? null,
                    'profiles' => $m->type === 'upi' ? $upiProfiles : [],
                ];
            });

        return response()->json([
            'data' => $methods
        ]);
    }

    public function createPayment(String $tenantSlug, String $orderId, InitiatePaymentRequest $request, PaymentService $service)
    {
        $startedAt = microtime(true);
        $order = Order::find($orderId);

        try {
            $payment = $service->createPayment(
                $order,
                $request->payment_method,
                $request->amount,
                $request->payment_method === 'upi' ? $request->upi_profile_id : null
            );

            $paymentModel = is_array($payment) ? ($payment['payment'] ?? null) : $payment;
            $alreadyPaid = is_array($payment) && ($payment['already_paid'] ?? false);

            Observability::logInfo('payment.created', [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'endpoint' => 'payment.create',
                'order_id' => $order?->id ?? $orderId,
                'payment_id' => $paymentModel?->id,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentModel?->status,
                'already_paid' => $alreadyPaid,
                'location_id' => $order?->location_id,
                'duration_ms' => Observability::durationMs($startedAt),
            ], $request);

            return response()->json($payment);
        } catch (Throwable $e) {
            Observability::logFailure('payment.create.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'endpoint' => 'payment.create',
                'order_id' => $orderId,
                'payment_method' => $request->payment_method,
                'location_id' => $order?->location_id ?? $request->input('location_id'),
                'duration_ms' => Observability::durationMs($startedAt),
                'action' => 'payment.create',
            ], $request);

            throw $e;
        }
    }

    private function maskUpiId(?string $upiId): ?string
    {
        if (! $upiId || ! str_contains($upiId, '@')) {
            return $upiId;
        }

        [$name, $handle] = explode('@', $upiId, 2);
        $prefix = substr($name, 0, min(3, strlen($name)));

        return $prefix.'****@'.$handle;
    }

    public function markSuccess(String $tenantSlug, String $paymentId, PaymentService $service, OrderKitchenDispatchService $kitchenDispatch)
    {
        $startedAt = microtime(true);
        $payment = Payment::findOrFail($paymentId);
        $order = $payment?->order;

        try {
            $result = $service->markPaymentSuccess($payment);
            $payment = $result['payment'];
            $order = $result['order'];

            if ($result['idempotent']) {
                $order = $order->fresh(['tableSession', 'token']);

                Observability::logInfo('payment.success.idempotent', [
                    'tenant_slug' => $tenantSlug,
                    'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                    'endpoint' => 'payment.success',
                    'order_id' => $order?->id,
                    'payment_id' => $payment?->id ?? $paymentId,
                    'payment_status' => $payment?->status,
                    'order_status' => $order?->status,
                    'already_paid' => true,
                    'already_successful' => $result['already_successful'],
                    'post_processing_required' => false,
                    'duration_ms' => Observability::durationMs($startedAt),
                ]);

                return response()->json([
                    'payment' => $payment,
                    'token' => $order?->token,
                    'order' => $order,
                    'already_paid' => true,
                    'invoice_generated' => false,
                    'invoice_id' => $order?->invoice_id,
                    'invoice_number' => $order?->invoice_no,
                    'invoice_url' => data_get($order?->meta, 'invoice.url'),
                ]);
            }

            $order = $order->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);
            $invoice = $this->autoGenerateTableServiceInvoice($tenantSlug, $order, $payment);

            $this->closeTableSessionAfterPayment($tenantSlug, $order, $payment);

            $token = $this->ensureTokenAfterPayment($tenantSlug, $order, $payment, $kitchenDispatch);
            $order = $order->fresh(['tableSession', 'token']);

            Observability::logInfo('payment.completed', [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'endpoint' => 'payment.success',
                'order_id' => $order?->id,
                'payment_id' => $payment?->id ?? $paymentId,
                'location_id' => $order?->location_id,
                'table_id' => $order?->table_id,
                'table_session_id' => $order?->table_session_id,
                'token_id' => $token?->id,
                'invoice_id' => $invoice['invoice_id'],
                'invoice_number' => $invoice['invoice_number'],
                'already_paid' => false,
                'duration_ms' => Observability::durationMs($startedAt),
            ]);

            return response()->json([
                'payment' => $payment->fresh(),
                'token' => $token,
                'order' => $order,
                'already_paid' => false,
                'invoice_generated' => $invoice['invoice_generated'],
                'invoice_id' => $invoice['invoice_id'],
                'invoice_number' => $invoice['invoice_number'],
                'invoice_url' => $invoice['invoice_url'],
            ]);
        } catch (Throwable $e) {
            Observability::logFailure('payment.success.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'endpoint' => 'payment.success',
                'order_id' => $order?->id,
                'payment_id' => $paymentId,
                'location_id' => $order?->location_id,
                'duration_ms' => Observability::durationMs($startedAt),
                'action' => 'payment.success',
            ]);

            throw $e;
        }
    }

    private function closeTableSessionAfterPayment(string $tenantSlug, Order $order, Payment $payment): void
    {
        if ($order->dining_flow !== 'table_service' || $order->payment_status !== 'paid') {
            return;
        }

        try {
            app(TableSessionService::class)->closeForOrder($order);
        } catch (Throwable $e) {
            Observability::logFailure('table_service.close_after_payment.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'table_session_id' => $order->table_session_id,
                'endpoint' => 'payment.success',
                'action' => 'table_service.close_after_payment',
            ]);
        }
    }

    private function ensureTokenAfterPayment(
        string $tenantSlug,
        Order $order,
        Payment $payment,
        OrderKitchenDispatchService $kitchenDispatch
    ) {
        try {
            return $kitchenDispatch->ensureTokenAndDispatchWhenReady($order, 'payment_success');
        } catch (Throwable $e) {
            Observability::logFailure('token.dispatch_after_payment.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'location_id' => $order->location_id,
                'endpoint' => 'payment.success',
                'action' => 'token.dispatch_after_payment',
            ]);

            return $order->token;
        }
    }

    private function autoGenerateTableServiceInvoice(string $tenantSlug, Order $order, Payment $payment): array
    {
        $invoice = [
            'invoice_generated' => false,
            'invoice_id' => $order->invoice_id,
            'invoice_number' => $order->invoice_no,
            'invoice_url' => data_get($order->meta, 'invoice.url'),
        ];

        if (! $this->shouldAutoGenerateTableServiceInvoice($order)) {
            return $invoice;
        }

        if ($order->invoice_id || $order->invoice_no) {
            $this->logInvoiceGenerated($tenantSlug, $order, $payment, false);
            return $invoice;
        }

        try {
            $startedAt = microtime(true);
            $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

            if (! $tenant) {
                throw new \RuntimeException('Tenant not resolved for table-service invoice generation');
            }

            $paperSize = $this->defaultInvoicePaperSize((string) $tenant->industry);
            $orderPayload = (new OrderResource($order))->resolve(request());

            app(InvoiceService::class)->generate(
                $orderPayload,
                $tenant,
                $tenant->industry,
                $paperSize
            );

            $freshOrder = $order->fresh();
            $this->logInvoiceGenerated($tenantSlug, $freshOrder, $payment, true, Observability::durationMs($startedAt));

            return [
                'invoice_generated' => true,
                'invoice_id' => $freshOrder->invoice_id,
                'invoice_number' => $freshOrder->invoice_no,
                'invoice_url' => data_get($freshOrder->meta, 'invoice.url'),
            ];
        } catch (Throwable $e) {
            $freshOrder = $order->fresh();

            Observability::logFailure('table_service.invoice.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'invoice_id' => $freshOrder?->invoice_id ?? $order->invoice_id,
                'location_id' => $order->location_id,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'invoice_connection' => Invoice::CENTRAL_CONNECTION,
                'default_connection' => DB::getDefaultConnection(),
                'action' => 'table_service.invoice.generate',
            ]);

            if ($freshOrder?->invoice_id || $freshOrder?->invoice_no) {
                return [
                    'invoice_generated' => true,
                    'invoice_id' => $freshOrder->invoice_id,
                    'invoice_number' => $freshOrder->invoice_no,
                    'invoice_url' => data_get($freshOrder->meta, 'invoice.url'),
                ];
            }

            return $invoice;
        }
    }

    private function shouldAutoGenerateTableServiceInvoice(Order $order): bool
    {
        return strtolower((string) $order->order_type) === 'dine_in'
            && $order->dining_flow === 'table_service'
            && $order->payment_status === 'paid'
            && $order->status === 'completed';
    }

    private function defaultInvoicePaperSize(string $industry): string
    {
        $industry = IndustryNormalizer::normalize($industry);
        $templates = config("invoice.industries.{$industry}.templates", []);

        return array_key_exists('80mm', $templates)
            ? '80mm'
            : ((string) array_key_first($templates) ?: 'a4');
    }

    private function logInvoiceGenerated(string $tenantSlug, Order $order, Payment $payment, bool $created, ?int $durationMs = null): void
    {
        try {
            Log::info('table_service.invoice.generated', Observability::context([
                'tenant_slug' => $tenantSlug,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'invoice_id' => $order->invoice_id,
                'invoice_number' => $order->invoice_no,
                'location_id' => $order->location_id,
                'created' => $created,
                'duration_ms' => $durationMs,
                'action' => 'table_service.invoice.generate',
            ]));
        } catch (Throwable) {
            // Invoice logging must never affect billing.
        }
    }
}
