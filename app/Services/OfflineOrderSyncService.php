<?php

namespace App\Services;

use App\Http\Resources\Tenant\OrderResource;
use App\Models\OfflineOrderSync;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Services\Orders\OrderService;
use App\Services\Payments\PaymentService;
use App\Support\Observability;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OfflineOrderSyncService
{
    private const STALE_PROCESSING_MINUTES = 15;

    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService,
        private OrderKitchenDispatchService $kitchenDispatch
    ) {
    }

    public function sync($tenant, array $payload): array
    {
        $sync = $this->firstOrCreateSyncRecord($tenant, $payload);

        if (! $sync->wasRecentlyCreated) {
            $this->logPayloadMismatchIfNeeded($sync, $payload);

            if ($sync->status === 'synced') {
                return $sync->response;
            }

            if ($sync->status === 'processing') {
                if (! $this->isStaleProcessing($sync)) {
                    return [
                        'success' => false,
                        'status' => 'processing',
                        'local_order_id' => $payload['local_order_id'],
                        'backend_order_id' => $sync->backend_order_id,
                        'message' => 'Offline order sync is already processing',
                    ];
                }

                Observability::logInfo('offline.sync.stale_processing_retried', [
                    'tenant_id' => $tenant->id,
                    'local_order_id' => $payload['local_order_id'],
                    'backend_order_id' => $sync->backend_order_id,
                    'stale_after_minutes' => self::STALE_PROCESSING_MINUTES,
                    'updated_at' => optional($sync->updated_at)->toISOString(),
                ]);
            }

            $sync->update([
                'status' => 'processing',
                'payload' => $payload,
                'response' => null,
                'error_message' => null,
                'synced_at' => null,
            ]);
        }

        try {
            $result = DB::transaction(function () use ($payload) {
                return $this->replayOrder($payload);
            });

            $response = $this->buildResponse($payload, $result['order'], $result['payment']);

            $sync->update([
                'backend_order_id' => $result['order']->id,
                'status' => 'synced',
                'response' => $response,
                'error_message' => null,
                'synced_at' => now(),
            ]);

            return $response;
        } catch (\Throwable $e) {
            $sync->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function firstOrCreateSyncRecord($tenant, array $payload): OfflineOrderSync
    {
        try {
            return OfflineOrderSync::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'local_order_id' => $payload['local_order_id'],
                ],
                [
                    'status' => 'processing',
                    'payload' => $payload,
                ]
            );
        } catch (QueryException $e) {
            $sync = OfflineOrderSync::where('tenant_id', $tenant->id)
                ->where('local_order_id', $payload['local_order_id'])
                ->first();

            if (! $sync) {
                throw $e;
            }

            Observability::logWarning('offline.sync.unique_collision_recovered', $e, [
                'tenant_id' => $tenant->id,
                'local_order_id' => $payload['local_order_id'],
                'status' => $sync->status,
                'backend_order_id' => $sync->backend_order_id,
            ]);

            $sync->wasRecentlyCreated = false;

            return $sync;
        }
    }

    private function isStaleProcessing(OfflineOrderSync $sync): bool
    {
        return $sync->updated_at
            && $sync->updated_at->lt(now()->subMinutes(self::STALE_PROCESSING_MINUTES));
    }

    private function logPayloadMismatchIfNeeded(OfflineOrderSync $sync, array $payload): void
    {
        $existingPayload = $sync->payload ?? [];

        if ($this->payloadHash($existingPayload) === $this->payloadHash($payload)) {
            return;
        }

        Observability::logInfo('offline.sync.payload_hash_mismatch', [
            'tenant_id' => $sync->tenant_id,
            'local_order_id' => $sync->local_order_id,
            'status' => $sync->status,
            'backend_order_id' => $sync->backend_order_id,
            'existing_payload_hash' => $this->payloadHash($existingPayload),
            'incoming_payload_hash' => $this->payloadHash($payload),
        ]);
    }

    private function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode($this->sortPayload($payload)));
    }

    private function sortPayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->sortPayload($value);
            }
        }

        ksort($payload);

        return $payload;
    }

    private function replayOrder(array $payload): array
    {
        $customerId = $this->resolveCustomerId($payload['customer'] ?? null);

        $order = $this->orderService->createDraft(
            $payload['location_id'],
            $customerId,
            $payload['order_type'] ?? 'pos',
            $payload['table_id'] ?? null,
            null,
            null,
            null,
            [
                'delivery_channel' => $payload['delivery_channel'] ?? null,
                'delivery_channel_label' => $payload['delivery_channel_label'] ?? null,
                'external_order_reference' => $payload['external_order_reference'] ?? null,
            ]
        );

        $this->applyOfflineOrderMetadata($order, $payload);
        $this->syncItems($order, $payload);
        $this->attachCustomerSnapshot($order, $payload['customer'] ?? null);
        $this->orderService->moveToPendingPayment($order->fresh());

        $payment = $this->createAndCompletePayment($order->fresh(), $payload);

        return [
            'order' => $order->fresh()->load('items.product', 'customer', 'location', 'payments', 'token'),
            'payment' => $payment->fresh(),
        ];
    }

    private function syncItems(Order $order, array $payload): void
    {
        $items = collect($payload['items'])
            ->map(fn (array $item) => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ])
            ->all();

        $this->orderService->syncItems($order, new Request([
            'items' => $items,
            'subtotal' => $payload['totals']['subtotal'],
            'tax' => $payload['totals']['tax_total'] ?? ($payload['tax_summary']['total_tax'] ?? 0),
            'discount' => $payload['totals']['discount_total'] ?? ($payload['discount']['amount'] ?? 0),
            'total' => $payload['totals']['grand_total'],
        ]));
    }

    private function createAndCompletePayment(Order $order, array $payload): Payment
    {
        $paymentPayload = $payload['payment'];

        $payment = $this->paymentService->createPayment(
            $order,
            $paymentPayload['method'],
            $paymentPayload['amount'],
            $paymentPayload['method'] === 'upi' ? ($paymentPayload['upi_profile_id'] ?? null) : null
        );

        if (is_array($payment)) {
            $payment = $payment['payment'];
        }

        $this->applyPaymentMetadata($payment, $payload);
        $paymentResult = $this->paymentService->markPaymentSuccess($payment->fresh());
        $payment = $paymentResult['payment'];

        if ($order->fresh()->status !== 'completed') {
            throw new \Exception('Offline payment did not fully complete the order');
        }

        $this->kitchenDispatch->ensureTokenAndDispatchWhenReady($order->fresh(), 'offline_order_synced');

        return $payment;
    }

    private function resolveCustomerId(?array $customer): ?int
    {
        if (! $customer) {
            return null;
        }

        if (! empty($customer['id'])) {
            return (int) $customer['id'];
        }

        if (! empty($customer['phone'])) {
            $existing = Customer::where('phone', $customer['phone'])->first();

            if ($existing) {
                return $existing->id;
            }
        }

        if (empty($customer['name']) && empty($customer['phone']) && empty($customer['email'])) {
            return null;
        }

        return Customer::create([
            'name' => $customer['name'] ?? null,
            'phone' => $customer['phone'] ?? null,
            'email' => $customer['email'] ?? null,
        ])->id;
    }

    private function attachCustomerSnapshot(Order $order, ?array $customer): void
    {
        if (! $customer) {
            return;
        }

        $order->update([
            'customer_name' => $customer['name'] ?? null,
            'customer_phone' => $customer['phone'] ?? null,
        ]);
    }

    private function applyOfflineOrderMetadata(Order $order, array $payload): void
    {
        $meta = $order->meta ?? [];

        $updates = [
            'ordered_at' => $payload['offline_created_at'] ?? now(),
            'notes' => $payload['notes'] ?? null,
            'paid_amount' => $payload['totals']['paid_amount'],
            'balance_due' => $payload['totals']['balance_amount'] ?? 0,
            'meta' => array_merge($meta, [
                'offline' => true,
                'local_order_id' => $payload['local_order_id'],
                'offline_created_at' => $payload['offline_created_at'] ?? null,
                'offline_invoice_number' => $payload['invoice']['offline_invoice_number'] ?? null,
                'offline_token_number' => $payload['token']['offline_token_number'] ?? null,
                'discount' => $payload['discount'] ?? null,
                'tax_summary' => $payload['tax_summary'] ?? null,
            ]),
        ];

        if (Schema::hasColumn('pos_orders', 'business_date') && ! empty($payload['offline_created_at'])) {
            $updates['business_date'] = Carbon::parse($payload['offline_created_at'])->toDateString();
        }

        $order->forceFill($updates)->save();
    }

    private function applyPaymentMetadata(Payment $payment, array $payload): void
    {
        $paymentPayload = $payload['payment'];
        $meta = $payment->meta ?? [];

        $payment->update([
            'transaction_id' => $paymentPayload['upi_transaction_id'] ?? $paymentPayload['reference'] ?? $payment->transaction_id,
            'provider_ref' => $paymentPayload['reference'] ?? $payment->provider_ref,
            'meta' => array_merge($meta, [
                'offline' => true,
                'local_order_id' => $payload['local_order_id'],
                'reference' => $paymentPayload['reference'] ?? null,
                'upi_transaction_id' => $paymentPayload['upi_transaction_id'] ?? null,
                'proof' => $paymentPayload['proof'] ?? null,
            ]),
        ]);
    }

    private function buildResponse(array $payload, Order $order, Payment $payment): array
    {
        $orderResource = (new OrderResource($order))->resolve();

        return [
            'success' => true,
            'status' => 'synced',
            'local_order_id' => $payload['local_order_id'],
            'backend_order_id' => $order->id,
            'invoice_id' => null,
            'invoice_number' => $order->invoice_no,
            'payment_id' => $payment->id,
            'token_id' => $order->token?->id,
            'token_number' => $order->token?->token_code,
            'message' => 'Offline order synced successfully',
            'order' => $orderResource,
        ];
    }
}
