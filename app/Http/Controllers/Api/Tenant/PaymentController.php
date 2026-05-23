<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\UpiProfile;
use Illuminate\Http\Request;
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
        $order = Order::find($orderId);

        try {
            return response()->json(
                $service->createPayment(
                    $order,
                    $request->payment_method,
                    $request->amount,
                    $request->payment_method === 'upi' ? $request->upi_profile_id : null
                )
            );
        } catch (Throwable $e) {
            Observability::logFailure('payment.create.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'order_id' => $orderId,
                'payment_method' => $request->payment_method,
                'location_id' => $order?->location_id ?? $request->input('location_id'),
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
        $payment = Payment::find($paymentId);
        $order = $payment?->order;

        try {
            $service->markPaymentSuccess($payment);

            $order = $order->fresh(['items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product']);
            $invoice = $this->autoGenerateTableServiceInvoice($tenantSlug, $order, $payment);

            if ($order->dining_flow === 'table_service') {
                app(TableSessionService::class)->closeForOrder($order);
            }

            $token = $kitchenDispatch->ensureTokenAndDispatchWhenReady($order, 'payment_success');
            $order = $order->fresh(['tableSession', 'token']);

            return response()->json([
                'payment' => $payment->fresh(),
                'token' => $token,
                'order' => $order,
                'invoice_generated' => $invoice['invoice_generated'],
                'invoice_id' => $invoice['invoice_id'],
                'invoice_number' => $invoice['invoice_number'],
                'invoice_url' => $invoice['invoice_url'],
            ]);
        } catch (Throwable $e) {
            Observability::logFailure('payment.success.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'order_id' => $order?->id,
                'payment_id' => $paymentId,
                'location_id' => $order?->location_id,
                'action' => 'payment.success',
            ]);

            throw $e;
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
            $this->logInvoiceGenerated($tenantSlug, $freshOrder, $payment, true);

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

    private function logInvoiceGenerated(string $tenantSlug, Order $order, Payment $payment, bool $created): void
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
                'action' => 'table_service.invoice.generate',
            ]));
        } catch (Throwable) {
            // Invoice logging must never affect billing.
        }
    }
}
