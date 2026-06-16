<?php

namespace App\Services\Bakery;

use App\Models\Tenant\BakeryOrder;
use App\Models\Tenant\BakeryOrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BakeryPaymentService
{
    public const METHODS = ['cash', 'upi', 'card', 'bank_transfer', 'other'];
    public const STATUSES = ['pending', 'success', 'failed', 'cancelled'];

    public function recordPayment(BakeryOrder $order, array $data): BakeryOrderPayment
    {
        $amount = $this->money($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Payment amount must be greater than zero.'],
            ]);
        }

        return DB::transaction(function () use ($order, $data, $amount) {
            $lockedOrder = BakeryOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'order' => ['Cancelled bakery order cannot accept payment.'],
                ]);
            }

            $payment = BakeryOrderPayment::create([
                'bakery_order_id' => $lockedOrder->id,
                'payment_method' => $this->normalizeMethod($data['payment_method'] ?? 'cash'),
                'amount' => $amount,
                'status' => $this->normalizeStatus($data['status'] ?? 'success'),
                'transaction_id' => $data['transaction_id'] ?? null,
                'provider' => $data['provider'] ?? null,
                'provider_ref' => $data['provider_ref'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'received_by' => $data['received_by'] ?? null,
                'meta' => $data['meta'] ?? null,
            ]);

            app(BakeryOrderService::class)->syncPaymentTotals($lockedOrder);

            return $payment->fresh();
        });
    }

    private function normalizeMethod(string $method): string
    {
        $normalized = strtolower(trim($method));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (! in_array($normalized, self::METHODS, true)) {
            throw ValidationException::withMessages([
                'payment_method' => ['Invalid bakery payment method.'],
            ]);
        }

        return $normalized;
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (! in_array($normalized, self::STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => ['Invalid bakery payment status.'],
            ]);
        }

        return $normalized;
    }

    private function money($amount): float
    {
        return round((float) $amount, 2);
    }
}
