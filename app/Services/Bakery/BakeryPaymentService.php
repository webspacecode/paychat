<?php

namespace App\Services\Bakery;

use App\Models\Tenant\BakeryOrder;
use App\Models\Tenant\BakeryOrderPayment;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BakeryPaymentService
{
    public const METHODS = ['cash', 'upi', 'card', 'bank_transfer', 'other'];
    public const STATUSES = ['pending', 'success', 'failed', 'cancelled'];

    public function __construct(private PaymentService $posPayments)
    {
    }

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

            $method = $this->normalizeMethod($data['payment_method'] ?? 'cash');
            $status = $this->normalizeStatus($data['status'] ?? ($method === 'upi' && ! empty($data['generate_qr']) ? 'pending' : 'success'));

            if ($method === 'upi' && $status === 'pending') {
                $existing = $lockedOrder->payments()
                    ->where('payment_method', 'upi')
                    ->where('status', 'pending')
                    ->where('amount', $amount)
                    ->latest('id')
                    ->first();

                if ($existing) {
                    return $existing->fresh();
                }
            }

            $remaining = $this->remainingBalance($lockedOrder);

            if (in_array($status, ['pending', 'success'], true) && $amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => ['Payment amount cannot exceed the remaining balance.'],
                ]);
            }

            $payment = BakeryOrderPayment::create([
                'bakery_order_id' => $lockedOrder->id,
                'payment_method' => $method,
                'amount' => $amount,
                'status' => $status,
                'transaction_id' => $data['transaction_id'] ?? null,
                'provider' => $data['provider'] ?? null,
                'provider_ref' => $data['provider_ref'] ?? null,
                'paid_at' => $status === 'success' ? ($data['paid_at'] ?? now()) : null,
                'received_by' => $data['received_by'] ?? null,
                'meta' => $data['meta'] ?? null,
            ]);

            if ($method === 'upi' && $status === 'pending') {
                $qr = $this->posPayments->createExternalUpiQrAttempt([
                    'amount' => $amount,
                    'location_id' => $lockedOrder->location_id,
                    'order_no' => $lockedOrder->bakery_order_no,
                    'payment_id' => $payment->id,
                    'source_type' => 'bakery_order',
                    'source_id' => $lockedOrder->id,
                    'upi_profile_id' => $data['upi_profile_id'] ?? null,
                ]);

                $payment->update([
                    'provider_ref' => $payment->provider_ref ?: ($qr['provider_ref'] ?? null),
                    'meta' => array_merge($payment->meta ?? [], [
                        'kind' => $data['meta']['kind'] ?? 'upi_qr_attempt',
                        'source' => 'bakery_order',
                        'upi_profile_id' => $qr['upi_profile_id'] ?? null,
                        'upi_qr_url' => $qr['upi_qr_url'] ?? null,
                        'upi_qr_generated_at' => now()->toIso8601String(),
                    ], $qr['meta'] ?? []),
                ]);
            }

            app(BakeryOrderService::class)->syncPaymentTotals($lockedOrder);

            return $payment->fresh();
        });
    }

    public function markPaymentSuccess(BakeryOrder $order, BakeryOrderPayment $payment, array $data = []): BakeryOrderPayment
    {
        return DB::transaction(function () use ($order, $payment, $data) {
            $lockedOrder = BakeryOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedPayment = BakeryOrderPayment::whereKey($payment->id)
                ->where('bakery_order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'order' => ['Cancelled bakery order cannot accept payment.'],
                ]);
            }

            if (in_array($lockedPayment->status, ['failed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'payment' => ['This payment attempt can no longer be marked as paid.'],
                ]);
            }

            if ($lockedPayment->status !== 'success') {
                $remaining = $this->remainingBalance($lockedOrder) + $this->money($lockedPayment->amount);
                if ($this->money($lockedPayment->amount) > $remaining) {
                    throw ValidationException::withMessages([
                        'amount' => ['Payment amount cannot exceed the remaining balance.'],
                    ]);
                }

                $lockedPayment->update([
                    'status' => 'success',
                    'transaction_id' => $data['transaction_id'] ?? $lockedPayment->transaction_id,
                    'paid_at' => $data['paid_at'] ?? now(),
                    'received_by' => $data['received_by'] ?? $lockedPayment->received_by,
                    'meta' => array_merge($lockedPayment->meta ?? [], [
                        'manually_confirmed_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            app(BakeryOrderService::class)->syncPaymentTotals($lockedOrder);

            return $lockedPayment->fresh();
        });
    }

    private function remainingBalance(BakeryOrder $order): float
    {
        $paidAmount = $this->money($order->payments()
            ->where('status', 'success')
            ->sum('amount'));

        $pendingAmount = $this->money($order->payments()
            ->whereIn('status', ['pending'])
            ->sum('amount'));

        return max(0, $this->money(($order->total_amount ?? $order->total ?? 0) - $paidAmount - $pendingAmount));
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
