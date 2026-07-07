<?php

namespace App\Services\Bakery;

use App\Models\Tenant\BakeryOrder;
use App\Models\Tenant\Location;
use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BakeryOrderService
{
    public const STATUSES = [
        'booked',
        'confirmed',
        'in_production',
        'ready',
        'delivered',
        'completed',
        'cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid',
        'partial',
        'paid',
    ];

    public const LEGACY_PAYMENT_STATUSES = [
        'advance_paid',
        'partially_paid',
        'fully_paid',
    ];

    public const ORDER_TYPES = [
        'custom_cake',
        'ready_cake_booking',
        'event_party',
        'other',
    ];

    public function __construct(private BakeryPaymentService $payments)
    {
    }

    public function list(array $filters = [], int $perPage = 20)
    {
        $perPage = max(1, min($perPage, 100));

        return BakeryOrder::query()
            ->with(['payments', 'items', 'customer:id,name,phone,email', 'location:id,name'])
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $this->normalizeStatus($status))
            )
            ->when($filters['payment_status'] ?? null, fn ($q, $status) =>
                $q->where('payment_status', $this->normalizePaymentStatus($status))
            )
            ->when($filters['date_from'] ?? null, fn ($q, $date) =>
                $q->whereDate('fulfillment_at', '>=', $date)
            )
            ->when($filters['date_to'] ?? null, fn ($q, $date) =>
                $q->whereDate('fulfillment_at', '<=', $date)
            )
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('bakery_order_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('fulfillment_at IS NULL')
            ->orderBy('fulfillment_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function productionBoard(array $filters = [])
    {
        return BakeryOrder::query()
            ->with(['payments', 'items', 'customer:id,name,phone,email', 'location:id,name'])
            ->whereIn('status', ['booked', 'confirmed', 'in_production', 'ready'])
            ->when($filters['date'] ?? null, fn ($q, $date) =>
                $q->whereDate('fulfillment_at', $date)
            )
            ->orderByRaw('fulfillment_at IS NULL')
            ->orderBy('fulfillment_at')
            ->get();
    }

    public function create(array $data, ?int $userId = null): BakeryOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $customer = $this->resolveCustomer($data);
            $items = (array) ($data['items'] ?? []);
            $totals = $this->totals($data, null, $items);
            $advancePaid = $this->money($data['advance_paid'] ?? 0);
            $paidAmount = min($advancePaid, $totals['total']);

            $order = BakeryOrder::create(array_merge(
                $this->orderPayload($data, $customer, $userId),
                $totals,
                [
                    'bakery_order_no' => $this->generateOrderNumber(),
                    'status' => $this->normalizeStatus($data['status'] ?? 'booked'),
                    'paid_amount' => $paidAmount,
                    'balance_due' => max(0, $this->money($totals['total'] - $paidAmount)),
                    'payment_status' => $this->paymentStatus($totals['total'], $paidAmount, $advancePaid > 0),
                ]
            ));

            $this->syncItems($order, $items);

            if ($advancePaid > 0) {
                $this->payments->recordPayment($order, [
                    'payment_method' => $data['advance_payment_method'] ?? $data['payment_method'] ?? 'cash',
                    'amount' => $advancePaid,
                    'status' => $data['advance_payment_status'] ?? 'success',
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'provider' => $data['provider'] ?? null,
                    'provider_ref' => $data['provider_ref'] ?? null,
                    'paid_at' => $data['paid_at'] ?? now(),
                    'received_by' => $userId,
                    'meta' => [
                        'kind' => 'advance',
                    ],
                ]);
            }

            return $order->fresh(['payments', 'items', 'customer', 'location']);
        });
    }

    public function update(BakeryOrder $order, array $data, ?int $userId = null): BakeryOrder
    {
        return DB::transaction(function () use ($order, $data, $userId) {
            $lockedOrder = BakeryOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $customer = $this->resolveCustomer($data, false);
            $payload = $this->orderPayload($data, $customer, $userId, false);

            if (array_key_exists('status', $data)) {
                $payload['status'] = $this->normalizeStatus($data['status']);
                $this->assertStatusTransitionAllowed($lockedOrder, $payload['status']);
            }

            $items = array_key_exists('items', $data) ? (array) $data['items'] : null;

            if ($this->hasTotalInput($data) || is_array($items)) {
                $totals = $this->totals($data, $lockedOrder, $items);
                $paidAmount = $this->syncEditedAdvancePayment($lockedOrder, $data, $userId);
                $payload = array_merge($payload, $totals, [
                    'paid_amount' => min($paidAmount, $totals['total']),
                    'balance_due' => max(0, $this->money($totals['total'] - $paidAmount)),
                    'payment_status' => $this->paymentStatus($totals['total'], $paidAmount, $paidAmount > 0),
                ]);
            }

            if ($payload) {
                $lockedOrder->update($payload);
            }

            if (is_array($items)) {
                $this->syncItems($lockedOrder, $items);
            }

            if (($payload['status'] ?? null) === 'completed') {
                $this->syncCompletedOrderToPos($lockedOrder->fresh(['items', 'payments', 'customer', 'location']), $userId);
            }

            return $lockedOrder->fresh(['payments', 'items', 'customer', 'location']);
        });
    }

    public function updateStatus(BakeryOrder $order, string $status, ?int $userId = null): BakeryOrder
    {
        return DB::transaction(function () use ($order, $status, $userId) {
            $lockedOrder = BakeryOrder::with(['items', 'payments', 'customer', 'location'])
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $normalizedStatus = $this->normalizeStatus($status);
            $this->assertStatusTransitionAllowed($lockedOrder, $normalizedStatus);

            $lockedOrder->update([
                'status' => $normalizedStatus,
                'updated_by' => $userId,
            ]);

            if ($normalizedStatus === 'completed') {
                $this->syncCompletedOrderToPos($lockedOrder->fresh(['items', 'payments', 'customer', 'location']), $userId);
            }

            return $lockedOrder->fresh(['payments', 'items', 'customer', 'location']);
        });
    }

    public function syncPaymentTotals(BakeryOrder $order): BakeryOrder
    {
        $successfulPayments = $order->payments()->where('status', 'success')->oldest('id')->get();
        $paidAmount = $this->money($successfulPayments->sum('amount'));
        $total = $this->money($order->total);
        $firstPaymentIsAdvance = data_get($successfulPayments->first()?->meta, 'kind') === 'advance';

        $order->update([
            'paid_amount' => min($paidAmount, $total),
            'balance_due' => max(0, $this->money($total - $paidAmount)),
            'total_amount' => $total,
            'payment_status' => $this->paymentStatus(
                $total,
                $paidAmount,
                $firstPaymentIsAdvance && $successfulPayments->count() === 1
            ),
        ]);

        return $order->fresh(['payments', 'items', 'customer', 'location']);
    }

    private function resolveCustomer(array $data, bool $createFromPhone = true): ?Customer
    {
        if (! empty($data['customer_id'])) {
            return Customer::find($data['customer_id']);
        }

        $phone = $this->normalizePhone($data['customer_phone'] ?? null);

        if ($phone) {
            $customer = Customer::where('phone', $phone)->first();

            if ($customer || ! $createFromPhone) {
                return $customer;
            }

            return Customer::create([
                'name' => $data['customer_name'] ?? null,
                'phone' => $phone,
            ]);
        }

        return null;
    }

    private function orderPayload(array $data, ?Customer $customer, ?int $userId, bool $creating = true): array
    {
        $allowed = [
            'location_id',
            'customer_name',
            'customer_phone',
            'order_type',
            'fulfillment_type',
            'fulfillment_at',
            'delivery_address',
            'cake_flavour',
            'weight',
            'flavour',
            'weight_value',
            'weight_unit',
            'cake_message',
            'design_notes',
            'reference_image_path',
            'notes',
            'meta',
        ];

        $payload = collect($data)
            ->only($allowed)
            ->filter(fn ($value) => $value !== null)
            ->all();

        if ($customer) {
            $payload['customer_id'] = $customer->id;
            $payload['customer_name'] = $customer->name ?? ($payload['customer_name'] ?? null);
            $payload['customer_phone'] = $customer->phone ?? ($payload['customer_phone'] ?? null);
        }

        if (isset($payload['customer_phone'])) {
            $payload['customer_phone'] = $this->normalizePhone($payload['customer_phone']) ?? $payload['customer_phone'];
        }

        $payload = $this->syncAliasFields($payload, $data);

        $payload['updated_by'] = $userId;

        if ($creating) {
            $payload['created_by'] = $userId;
            $payload['order_type'] = $this->normalizeOrderType($payload['order_type'] ?? 'custom_cake');
            $payload['fulfillment_type'] = $payload['fulfillment_type'] ?? 'pickup';
        } elseif (array_key_exists('order_type', $payload)) {
            $payload['order_type'] = $this->normalizeOrderType($payload['order_type']);
        }

        return $payload;
    }

    private function totals(array $data, ?BakeryOrder $order = null, ?array $items = null): array
    {
        $itemsTotal = is_array($items) ? $this->itemsTotal($items) : null;
        $subtotal = $this->money($data['subtotal'] ?? $itemsTotal ?? $order?->subtotal ?? 0);
        $discount = $this->money($data['discount'] ?? $order?->discount ?? 0);
        $tax = $this->money($data['tax'] ?? $order?->tax ?? 0);
        $shipping = $this->money($data['shipping'] ?? $order?->shipping ?? 0);
        if (array_key_exists('total_amount', $data)) {
            $total = $this->money($data['total_amount']);
        } elseif (array_key_exists('total', $data)) {
            $total = $this->money($data['total']);
        } else {
            $total = $this->money($subtotal - $discount + $tax + $shipping);
        }

        if ($total < 0) {
            throw ValidationException::withMessages([
                'total' => ['Total cannot be negative.'],
            ]);
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'total_amount' => $total,
        ];
    }

    private function hasTotalInput(array $data): bool
    {
        return (bool) array_intersect(array_keys($data), ['subtotal', 'discount', 'tax', 'shipping', 'total', 'total_amount']);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'BKO-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (BakeryOrder::where('bakery_order_no', $number)->exists());

        return $number;
    }

    private function syncCompletedOrderToPos(BakeryOrder $order, ?int $userId = null): void
    {
        $meta = $order->meta ?: [];
        $existingPosOrderId = data_get($meta, 'pos_order_id');

        $locationId = $order->location_id
            ?: Location::where('type', 'default')->value('id')
            ?: Location::query()->value('id');

        if (! $locationId) {
            throw ValidationException::withMessages([
                'location_id' => ['A location is required before this bakery order can be converted to a sales order.'],
            ]);
        }

        $total = $this->money($order->total_amount ?? $order->total ?? 0);
        $paidAmount = $this->money($order->paid_amount ?? 0);
        $paymentStatus = $paidAmount >= $total && $total > 0 ? 'paid' : ($paidAmount > 0 ? 'partially_paid' : 'unpaid');
        $businessDate = now()->toDateString();
        $completedAt = now();

        $payload = [
            'order_no' => $this->generatePosOrderNumber($order),
            'location_id' => $locationId,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'created_by' => $order->created_by ?? $userId,
            'updated_by' => $userId,
            'completed_by' => $userId,
            'order_type' => $order->fulfillment_type === 'delivery' ? 'delivery' : 'takeaway',
            'status' => 'completed',
            'payment_status' => $paymentStatus,
            'subtotal' => $this->money($order->subtotal ?? $total),
            'discount' => $this->money($order->discount ?? 0),
            'tax' => $this->money($order->tax ?? 0),
            'service_charge' => 0,
            'rounding' => 0,
            'total' => $total,
            'paid_amount' => min($paidAmount, $total),
            'balance_due' => max(0, $this->money($total - $paidAmount)),
            'paid_at' => $paidAmount > 0 ? $completedAt : null,
            'completed_at' => $completedAt,
            'business_date' => $businessDate,
            'notes' => trim("Bakery order {$order->bakery_order_no}\n".($order->notes ?? '')),
            'meta' => [
                'source' => 'bakery_management',
                'bakery_order_id' => $order->id,
                'bakery_order_no' => $order->bakery_order_no,
                'cake_flavour' => $order->cake_flavour,
                'weight' => $order->weight,
                'cake_message' => $order->cake_message,
            ],
        ];

        if (Schema::hasColumn('pos_orders', 'source')) {
            $payload['source'] = 'bakery_management';
        }

        if (Schema::hasColumn('pos_orders', 'delivery_channel')) {
            $payload['delivery_channel'] = 'bakery';
        }

        if (Schema::hasColumn('pos_orders', 'delivery_channel_label')) {
            $payload['delivery_channel_label'] = 'Bakery Management';
        }

        if (Schema::hasColumn('pos_orders', 'external_order_reference')) {
            $payload['external_order_reference'] = $order->bakery_order_no;
        }

        $payload = $this->filterPayloadForTable('pos_orders', $payload);
        $posOrder = $existingPosOrderId
            ? Order::whereKey($existingPosOrderId)->first()
            : null;

        if ($posOrder) {
            $posOrder->update($payload);
        } else {
            $posOrder = Order::create($payload);
        }

        if (Schema::hasTable('pos_order_items')) {
            $posOrder->items()->delete();

            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $posOrder->items()->create($this->filterPayloadForTable('pos_order_items', [
                    'product_id' => $item->product_id,
                    'quantity' => max(1, (int) ceil((float) $item->quantity)),
                    'price' => $this->money($item->unit_price),
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $this->money($item->line_total),
                ]));
            }
        }

        if ($paidAmount > 0 && Schema::hasTable('pos_payments')) {
            $posOrder->payments()->delete();

            foreach ($order->payments()->where('status', 'success')->oldest('id')->get() as $bakeryPayment) {
                $paymentMeta = array_merge($bakeryPayment->meta ?? [], [
                    'source' => 'bakery_management',
                    'bakery_order_id' => $order->id,
                    'bakery_order_payment_id' => $bakeryPayment->id,
                    'kind' => 'bakery_payment_sync',
                ]);

                Payment::create($this->filterPayloadForTable('pos_payments', [
                    'order_id' => $posOrder->id,
                    'payment_method' => in_array($bakeryPayment->payment_method, ['cash', 'upi'], true)
                        ? $bakeryPayment->payment_method
                        : 'cash',
                    'mode' => $bakeryPayment->payment_method === 'upi' ? 'personal' : null,
                    'provider' => $bakeryPayment->provider,
                    'provider_ref' => $bakeryPayment->provider_ref,
                    'transaction_id' => $bakeryPayment->transaction_id,
                    'upi_profile_id' => data_get($bakeryPayment->meta, 'upi_profile_id'),
                    'upi_qr_url' => data_get($bakeryPayment->meta, 'upi_qr_url'),
                    'amount' => $this->money($bakeryPayment->amount),
                    'status' => 'success',
                    'collected_by' => $bakeryPayment->received_by ?? $userId,
                    'meta' => $paymentMeta,
                ]));
            }
        }

        $meta['pos_order_id'] = $posOrder->id;
        $meta['pos_order_no'] = $posOrder->order_no;
        $meta['pos_synced_at'] = now()->toIso8601String();

        $order->update(['meta' => $meta]);
    }

    private function assertStatusTransitionAllowed(BakeryOrder $order, string $status): void
    {
        if ($order->status === 'cancelled' && $status !== 'cancelled') {
            throw ValidationException::withMessages([
                'status' => ['Cancelled bakery order cannot be reopened from this flow.'],
            ]);
        }

        if ($status !== 'completed') {
            return;
        }

        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => ['Cancelled bakery order cannot be completed.'],
            ]);
        }

        $total = $this->money($order->total_amount ?? $order->total ?? 0);
        $paid = $this->money($order->payments()->where('status', 'success')->sum('amount'));

        if ($total > 0 && $paid < $total) {
            throw ValidationException::withMessages([
                'payment_status' => ['Bakery order must be fully paid before completion.'],
            ]);
        }
    }

    private function filterPayloadForTable(string $table, array $payload): array
    {
        if (! Schema::hasTable($table)) {
            return $payload;
        }

        return collect($payload)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }

    private function syncEditedAdvancePayment(BakeryOrder $order, array $data, ?int $userId = null): float
    {
        $currentPaid = $this->money($order->payments()->where('status', 'success')->sum('amount'));

        if (! array_key_exists('advance_paid', $data)) {
            return $currentPaid;
        }

        $requestedPaid = $this->money($data['advance_paid']);
        $delta = $this->money($requestedPaid - $currentPaid);

        if ($delta <= 0) {
            return $currentPaid;
        }

        $this->payments->recordPayment($order, [
            'payment_method' => $data['advance_payment_method'] ?? $data['payment_method'] ?? 'cash',
            'amount' => $delta,
            'status' => $data['advance_payment_status'] ?? 'success',
            'transaction_id' => $data['transaction_id'] ?? null,
            'provider' => $data['provider'] ?? null,
            'provider_ref' => $data['provider_ref'] ?? null,
            'paid_at' => $data['paid_at'] ?? now(),
            'received_by' => $userId,
            'meta' => [
                'kind' => 'advance_adjustment',
            ],
        ]);

        return $requestedPaid;
    }

    private function generatePosOrderNumber(BakeryOrder $order): string
    {
        $base = substr('SALE-'.$order->bakery_order_no, 0, 46);
        $candidate = $base;
        $suffix = 1;

        while (Order::where('order_no', $candidate)->exists()) {
            $candidate = substr($base, 0, 43).'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    public function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (! in_array($normalized, self::STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => ['Invalid bakery order status.'],
            ]);
        }

        return $normalized;
    }

    public function normalizePaymentStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (! in_array($normalized, array_merge(self::PAYMENT_STATUSES, self::LEGACY_PAYMENT_STATUSES), true)) {
            throw ValidationException::withMessages([
                'payment_status' => ['Invalid bakery payment status.'],
            ]);
        }

        return $normalized;
    }

    private function paymentStatus(float $total, float $paidAmount, bool $hadAdvance): string
    {
        $total = $this->money($total);
        $paidAmount = $this->money($paidAmount);

        if ($paidAmount <= 0) {
            return 'unpaid';
        }

        if ($paidAmount >= $total && $total > 0) {
            return 'paid';
        }

        return 'partial';
    }

    private function normalizeOrderType(string $orderType): string
    {
        $normalized = strtolower(trim($orderType));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (! in_array($normalized, self::ORDER_TYPES, true)) {
            throw ValidationException::withMessages([
                'order_type' => ['Invalid bakery order type.'],
            ]);
        }

        return $normalized;
    }

    private function syncAliasFields(array $payload, array $data): array
    {
        if (! array_key_exists('cake_flavour', $payload) && array_key_exists('flavour', $data)) {
            $payload['cake_flavour'] = $data['flavour'];
        }

        if (! array_key_exists('flavour', $payload) && array_key_exists('cake_flavour', $data)) {
            $payload['flavour'] = $data['cake_flavour'];
        }

        if (! array_key_exists('weight', $payload) && (array_key_exists('weight_value', $data) || array_key_exists('weight_unit', $data))) {
            $payload['weight'] = trim(((string) ($data['weight_value'] ?? '')).' '.((string) ($data['weight_unit'] ?? ''))) ?: null;
        }

        return $payload;
    }

    private function syncItems(BakeryOrder $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $item) {
            $payload = $this->itemPayload($item);

            if ($payload) {
                $order->items()->create($payload);
            }
        }
    }

    private function itemPayload(array $item): ?array
    {
        $product = ! empty($item['product_id'])
            ? Product::with('images')->find($item['product_id'])
            : null;

        $productName = $item['product_name'] ?? $product?->name;

        if (! $productName) {
            return null;
        }

        $quantity = max(0, $this->money($item['quantity'] ?? 1));
        $unitPrice = $this->money($item['unit_price'] ?? $product?->price ?? 0);
        $lineTotal = array_key_exists('line_total', $item)
            ? $this->money($item['line_total'])
            : $this->money($quantity * $unitPrice);

        return [
            'product_id' => $product?->id,
            'product_name' => $productName,
            'sku' => $item['sku'] ?? $product?->sku,
            'quantity' => $quantity ?: 1,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'meta' => $item['meta'] ?? null,
        ];
    }

    private function itemsTotal(array $items): float
    {
        return $this->money(collect($items)->sum(function ($item) {
            $quantity = max(0, (float) ($item['quantity'] ?? 1));
            $unitPrice = array_key_exists('unit_price', $item)
                ? (float) $item['unit_price']
                : (float) Product::whereKey($item['product_id'] ?? null)->value('price');

            return array_key_exists('line_total', $item)
                ? (float) $item['line_total']
                : ($quantity * $unitPrice);
        }));
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits !== '' ? $digits : $phone;
    }

    private function money($amount): float
    {
        return round((float) $amount, 2);
    }
}
