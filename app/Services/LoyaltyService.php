<?php

namespace App\Services;

use App\Models\Tenant\Customer;
use App\Models\Tenant\LoyaltyTransaction;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoyaltyService
{
    public const SETTING_KEY = 'loyalty';

    public const DEFAULT_SETTINGS = [
        'enabled' => true,
        'points_per_100' => 1,
        'minimum_redemption' => 50,
        'redeem_value_per_point' => 1,
        'earn_on_discounted_total' => true,
    ];

    private const EXCLUDED_ORDER_STATUSES = ['draft', 'cancelled', 'void', 'refunded'];

    public function settings(): array
    {
        if (! Schema::hasTable('settings')) {
            return self::DEFAULT_SETTINGS;
        }

        $settings = Setting::get(self::SETTING_KEY, null, self::DEFAULT_SETTINGS);

        if (! is_array($settings)) {
            $settings = [];
        }

        return $this->normalizeSettings(array_merge(self::DEFAULT_SETTINGS, $settings));
    }

    public function updateSettings(array $settings): array
    {
        $settings = $this->normalizeSettings(array_merge($this->settings(), $settings));

        Setting::set(self::SETTING_KEY, $settings, 'json');

        return $settings;
    }

    public function awardForCompletedOrder(Order $order): ?LoyaltyTransaction
    {
        $settings = $this->settings();

        if (! $settings['enabled'] || ! $this->isEligibleOrder($order)) {
            return null;
        }

        $points = $this->pointsForOrder($order, $settings);

        if ($points <= 0) {
            return null;
        }

        try {
            return DB::transaction(function () use ($order, $points) {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedOrder || ! $this->isEligibleOrder($lockedOrder)) {
                    return null;
                }

                $existing = LoyaltyTransaction::query()
                    ->where('order_id', $lockedOrder->id)
                    ->where('type', 'earn')
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }

                $customer = Customer::query()
                    ->whereKey($lockedOrder->customer_id)
                    ->lockForUpdate()
                    ->first();

                if (! $customer) {
                    return null;
                }

                $balanceAfter = (int) $customer->loyalty_points + $points;
                $completedAt = $lockedOrder->completed_at ?: now();

                $transaction = LoyaltyTransaction::create([
                    'customer_id' => $customer->id,
                    'order_id' => $lockedOrder->id,
                    'type' => 'earn',
                    'points' => $points,
                    'amount' => $lockedOrder->total,
                    'balance_after' => $balanceAfter,
                    'description' => 'Points earned from order '.$lockedOrder->order_no,
                    'meta' => [
                        'order_no' => $lockedOrder->order_no,
                        'order_total' => (float) $lockedOrder->total,
                    ],
                    'created_by' => auth()->id(),
                ]);

                $customer->forceFill([
                    'loyalty_points' => $balanceAfter,
                    'total_visits' => (int) $customer->total_visits + 1,
                    'total_spend' => round((float) $customer->total_spend + (float) $lockedOrder->total, 2),
                    'last_visit_at' => $completedAt,
                ])->save();

                return $transaction;
            });
        } catch (QueryException $exception) {
            if ($this->isDuplicateTransaction($exception)) {
                return LoyaltyTransaction::query()
                    ->where('order_id', $order->id)
                    ->where('type', 'earn')
                    ->first();
            }

            throw $exception;
        }
    }

    public function isEligibleOrder(Order $order): bool
    {
        if (! $order->customer_id) {
            return false;
        }

        if ($order->payment_status !== 'paid') {
            return false;
        }

        if ($order->status !== 'completed') {
            return false;
        }

        return ! in_array($order->status, self::EXCLUDED_ORDER_STATUSES, true);
    }

    private function pointsForOrder(Order $order, array $settings): int
    {
        $amount = max(0, (float) $order->total);
        $pointsPer100 = max(0, (int) $settings['points_per_100']);

        return (int) floor($amount / 100) * $pointsPer100;
    }

    private function normalizeSettings(array $settings): array
    {
        return [
            'enabled' => (bool) ($settings['enabled'] ?? self::DEFAULT_SETTINGS['enabled']),
            'points_per_100' => max(0, (int) ($settings['points_per_100'] ?? self::DEFAULT_SETTINGS['points_per_100'])),
            'minimum_redemption' => max(0, (int) ($settings['minimum_redemption'] ?? self::DEFAULT_SETTINGS['minimum_redemption'])),
            'redeem_value_per_point' => max(0, (float) ($settings['redeem_value_per_point'] ?? self::DEFAULT_SETTINGS['redeem_value_per_point'])),
            'earn_on_discounted_total' => (bool) ($settings['earn_on_discounted_total'] ?? self::DEFAULT_SETTINGS['earn_on_discounted_total']),
        ];
    }

    private function isDuplicateTransaction(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true);
    }
}
