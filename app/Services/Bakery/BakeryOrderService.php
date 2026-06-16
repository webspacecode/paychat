<?php

namespace App\Services\Bakery;

use App\Models\Tenant\BakeryOrder;
use App\Models\Tenant\Customer;
use Illuminate\Support\Facades\DB;
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
        'advance_paid',
        'partially_paid',
        'fully_paid',
    ];

    public function __construct(private BakeryPaymentService $payments)
    {
    }

    public function list(array $filters = [], int $perPage = 20)
    {
        $perPage = max(1, min($perPage, 100));

        return BakeryOrder::query()
            ->with(['payments', 'customer:id,name,phone,email', 'location:id,name'])
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
            ->with(['payments', 'customer:id,name,phone,email', 'location:id,name'])
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
            $totals = $this->totals($data);
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

            return $order->fresh(['payments', 'customer', 'location']);
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
            }

            if ($this->hasTotalInput($data)) {
                $totals = $this->totals($data, $lockedOrder);
                $paidAmount = $this->money($lockedOrder->payments()->where('status', 'success')->sum('amount'));
                $payload = array_merge($payload, $totals, [
                    'paid_amount' => min($paidAmount, $totals['total']),
                    'balance_due' => max(0, $this->money($totals['total'] - $paidAmount)),
                    'payment_status' => $this->paymentStatus($totals['total'], $paidAmount, $paidAmount > 0),
                ]);
            }

            if ($payload) {
                $lockedOrder->update($payload);
            }

            return $lockedOrder->fresh(['payments', 'customer', 'location']);
        });
    }

    public function updateStatus(BakeryOrder $order, string $status, ?int $userId = null): BakeryOrder
    {
        $order->update([
            'status' => $this->normalizeStatus($status),
            'updated_by' => $userId,
        ]);

        return $order->fresh(['payments', 'customer', 'location']);
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
            'payment_status' => $this->paymentStatus(
                $total,
                $paidAmount,
                $firstPaymentIsAdvance && $successfulPayments->count() === 1
            ),
        ]);

        return $order->fresh(['payments', 'customer', 'location']);
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
            'fulfillment_type',
            'fulfillment_at',
            'delivery_address',
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

        $payload['updated_by'] = $userId;

        if ($creating) {
            $payload['created_by'] = $userId;
            $payload['fulfillment_type'] = $payload['fulfillment_type'] ?? 'pickup';
        }

        return $payload;
    }

    private function totals(array $data, ?BakeryOrder $order = null): array
    {
        $subtotal = $this->money($data['subtotal'] ?? $order?->subtotal ?? 0);
        $discount = $this->money($data['discount'] ?? $order?->discount ?? 0);
        $tax = $this->money($data['tax'] ?? $order?->tax ?? 0);
        $shipping = $this->money($data['shipping'] ?? $order?->shipping ?? 0);
        $total = array_key_exists('total', $data)
            ? $this->money($data['total'])
            : $this->money($subtotal - $discount + $tax + $shipping);

        if ($total < 0) {
            throw ValidationException::withMessages([
                'total' => ['Total cannot be negative.'],
            ]);
        }

        return compact('subtotal', 'discount', 'tax', 'shipping', 'total');
    }

    private function hasTotalInput(array $data): bool
    {
        return (bool) array_intersect(array_keys($data), ['subtotal', 'discount', 'tax', 'shipping', 'total']);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'BKO-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (BakeryOrder::where('bakery_order_no', $number)->exists());

        return $number;
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

        if (! in_array($normalized, self::PAYMENT_STATUSES, true)) {
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
            return 'fully_paid';
        }

        return $hadAdvance ? 'advance_paid' : 'partially_paid';
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
