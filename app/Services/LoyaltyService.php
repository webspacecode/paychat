<?php

namespace App\Services;

use App\Models\Tenant\Customer;
use App\Models\Tenant\LoyaltyRewardToken;
use App\Models\Tenant\LoyaltyTransaction;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LoyaltyService
{
    public const SETTING_KEY = 'loyalty';

    public const DEFAULT_SETTINGS = [
        'enabled' => true,
        'points_per_100' => 1,
        'minimum_redemption' => 50,
        'reward_threshold' => 100,
        'reward_tiers' => [
            [
                'id' => 'reward_100',
                'points_required' => 100,
                'label' => '₹50 off',
                'reward_text' => 'Redeem 100 points for ₹50 off',
                'application_type' => 'fixed_discount',
                'discount_amount' => 50,
                'product_id' => null,
                'product_name' => null,
                'active' => true,
            ],
            [
                'id' => 'reward_150',
                'points_required' => 150,
                'label' => '₹100 off',
                'reward_text' => 'Redeem 150 points for ₹100 off',
                'application_type' => 'fixed_discount',
                'discount_amount' => 100,
                'product_id' => null,
                'product_name' => null,
                'active' => true,
            ],
        ],
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

    public function sharePayload(?Customer $customer, ?LoyaltyTransaction $award = null): ?array
    {
        if (! $customer) {
            return null;
        }

        $settings = $this->settings();
        $balance = (int) $customer->loyalty_points;
        $threshold = (int) $settings['reward_threshold'];
        $eligible = $threshold > 0 && $balance >= $threshold;
        $rewardLink = $eligible ? $this->rewardLinkForCustomer($customer, $settings) : null;

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ],
            'loyalty_balance' => $balance,
            'points_earned_for_order' => $award ? (int) $award->points : 0,
            'reward_eligible' => $eligible,
            'reward_threshold' => $threshold,
            'reward_link' => $rewardLink,
            'reward_tiers' => $this->eligibleRewardTiers($balance, $settings),
        ];
    }

    public function rewardLinkForCustomer(Customer $customer, ?array $settings = null): ?string
    {
        $settings ??= $this->settings();

        if (! Schema::hasTable('loyalty_reward_tokens')) {
            return null;
        }

        if (! $this->eligibleRewardTiers((int) $customer->loyalty_points, $settings)) {
            return null;
        }

        $plainToken = $this->generateRewardToken();
        LoyaltyRewardToken::create([
            'customer_id' => $customer->id,
            'token_hash' => $this->hashRewardToken($plainToken),
            'meta' => [
                'created_for_balance' => (int) $customer->loyalty_points,
            ],
        ]);

        return url('/loyalty/rewards/'.$plainToken);
    }

    public function rewardPayloadForToken(string $token): ?array
    {
        if (! Schema::hasTable('loyalty_reward_tokens')) {
            return null;
        }

        $token = $this->normalizeRewardToken($token);

        $record = LoyaltyRewardToken::query()
            ->with('customer')
            ->where('token_hash', $this->hashRewardToken($token))
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $record || ! $record->customer) {
            return null;
        }

        $record->forceFill(['last_viewed_at' => now()])->save();

        $settings = $this->settings();
        $customer = $record->customer->fresh();
        $balance = (int) $customer->loyalty_points;
        $tiers = $this->eligibleRewardTiers($balance, $settings);

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'masked_phone' => $this->maskPhone($customer->phone),
            ],
            'loyalty_balance' => $balance,
            'reward_threshold' => (int) $settings['reward_threshold'],
            'reward_eligible' => count($tiers) > 0,
            'reward_tiers' => $tiers,
            'qr_payload' => 'paychat-loyalty:'.$token,
        ];
    }

    public function redeem(Customer $customer, array $payload): array
    {
        if (! Schema::hasTable('loyalty_transactions') || ! Schema::hasTable('loyalty_reward_tokens')) {
            throw ValidationException::withMessages([
                'qr_token' => ['Loyalty redemption is not available for this tenant.'],
            ]);
        }

        $token = $this->normalizeRewardToken((string) ($payload['qr_token'] ?? ''));
        $tokenHash = $this->hashRewardToken($token);
        $rewardTierId = (string) ($payload['reward_tier_id'] ?? '');
        $idempotencyKey = trim((string) ($payload['idempotency_key'] ?? ''));
        $redemptionKey = hash('sha256', implode('|', [
            $customer->id,
            $rewardTierId,
            $tokenHash,
            $idempotencyKey,
        ]));

        $existing = LoyaltyTransaction::query()
            ->where('redemption_key', $redemptionKey)
            ->first();

        if ($existing) {
            return $this->redemptionResponse($customer->fresh(), $existing);
        }

        return DB::transaction(function () use ($customer, $tokenHash, $rewardTierId, $payload, $redemptionKey) {
            $lockedCustomer = Customer::query()
                ->whereKey($customer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $tokenRecord = LoyaltyRewardToken::query()
                ->where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if (! $tokenRecord || (int) $tokenRecord->customer_id !== (int) $lockedCustomer->id) {
                throw ValidationException::withMessages([
                    'qr_token' => ['Reward QR is invalid for this customer.'],
                ]);
            }

            $duplicate = LoyaltyTransaction::query()
                ->where('redemption_key', $redemptionKey)
                ->lockForUpdate()
                ->first();

            if ($duplicate) {
                return $this->redemptionResponse($lockedCustomer->fresh(), $duplicate);
            }

            if ($tokenRecord->revoked_at || ($tokenRecord->expires_at && $tokenRecord->expires_at->isPast())) {
                throw ValidationException::withMessages([
                    'qr_token' => ['Reward QR is invalid or expired.'],
                ]);
            }

            $settings = $this->settings();
            $tier = collect($settings['reward_tiers'] ?? [])
                ->first(fn ($item) => (string) ($item['id'] ?? '') === $rewardTierId && (bool) ($item['active'] ?? true));

            if (! $tier) {
                throw ValidationException::withMessages([
                    'reward_tier_id' => ['Selected reward option is not available.'],
                ]);
            }

            $pointsRequired = (int) ($tier['points_required'] ?? 0);

            if ($pointsRequired <= 0 || (int) $lockedCustomer->loyalty_points < $pointsRequired) {
                throw ValidationException::withMessages([
                    'reward_tier_id' => ['Customer does not have enough loyalty points for this reward.'],
                ]);
            }

            $balanceAfter = (int) $lockedCustomer->loyalty_points - $pointsRequired;

            $transaction = LoyaltyTransaction::create([
                'customer_id' => $lockedCustomer->id,
                'order_id' => $payload['order_id'] ?? null,
                'type' => 'redeem',
                'points' => -$pointsRequired,
                'amount' => null,
                'balance_after' => $balanceAfter,
                'description' => 'Points redeemed: '.($tier['label'] ?? $rewardTierId),
                'meta' => [
                    'reward_tier_id' => $rewardTierId,
                    'reward_tier' => $tier,
                    'points_spent' => $pointsRequired,
                    'application_type' => $tier['application_type'] ?? 'fixed_discount',
                    'discount_amount' => $tier['discount_amount'] ?? 0,
                    'product_id' => $tier['product_id'] ?? null,
                    'product_name' => $tier['product_name'] ?? null,
                    'token_id' => $tokenRecord->id,
                ],
                'created_by' => auth()->id(),
                'redemption_key' => $redemptionKey,
            ]);

            $lockedCustomer->forceFill([
                'loyalty_points' => $balanceAfter,
            ])->save();

            $tokenRecord->forceFill([
                'revoked_at' => now(),
                'meta' => array_merge($tokenRecord->meta ?? [], [
                    'redeemed_transaction_id' => $transaction->id,
                    'redeemed_at' => now()->toISOString(),
                ]),
            ])->save();

            return $this->redemptionResponse($lockedCustomer->fresh(), $transaction);
        });
    }

    public function voidRedemption(Customer $customer, LoyaltyTransaction $redemption): array
    {
        if ($redemption->type !== 'redeem' || (int) $redemption->customer_id !== (int) $customer->id) {
            throw ValidationException::withMessages([
                'transaction' => ['Selected redemption was not found for this customer.'],
            ]);
        }

        $meta = $redemption->meta ?? [];

        if (! empty($meta['voided_transaction_id'])) {
            $existing = LoyaltyTransaction::query()->find($meta['voided_transaction_id']);
            if ($existing) {
                return $this->redemptionResponse($customer->fresh(), $existing);
            }
        }

        return DB::transaction(function () use ($customer, $redemption) {
            $lockedCustomer = Customer::query()
                ->whereKey($customer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRedemption = LoyaltyTransaction::query()
                ->whereKey($redemption->id)
                ->lockForUpdate()
                ->firstOrFail();

            $meta = $lockedRedemption->meta ?? [];

            if (! empty($meta['voided_transaction_id'])) {
                $existing = LoyaltyTransaction::query()->find($meta['voided_transaction_id']);
                if ($existing) {
                    return $this->redemptionResponse($lockedCustomer->fresh(), $existing);
                }
            }

            $pointsToRestore = abs((int) $lockedRedemption->points);
            $balanceAfter = (int) $lockedCustomer->loyalty_points + $pointsToRestore;

            $voidTransaction = LoyaltyTransaction::create([
                'customer_id' => $lockedCustomer->id,
                'order_id' => $lockedRedemption->order_id,
                'type' => 'redeem_void',
                'points' => $pointsToRestore,
                'amount' => null,
                'balance_after' => $balanceAfter,
                'description' => 'Redemption reverted: '.$lockedRedemption->description,
                'meta' => [
                    'voided_redemption_id' => $lockedRedemption->id,
                    'reward_tier_id' => $meta['reward_tier_id'] ?? null,
                    'reward_tier' => $meta['reward_tier'] ?? null,
                    'points_restored' => $pointsToRestore,
                ],
                'created_by' => auth()->id(),
                'redemption_key' => hash('sha256', 'void:'.$lockedRedemption->redemption_key),
            ]);

            $lockedCustomer->forceFill([
                'loyalty_points' => $balanceAfter,
            ])->save();

            $tokenId = $meta['token_id'] ?? null;
            if ($tokenId) {
                LoyaltyRewardToken::query()
                    ->whereKey($tokenId)
                    ->update(['revoked_at' => null]);
            }

            $lockedRedemption->forceFill([
                'meta' => array_merge($meta, [
                    'voided_transaction_id' => $voidTransaction->id,
                    'voided_at' => now()->toISOString(),
                ]),
            ])->save();

            return $this->redemptionResponse($lockedCustomer->fresh(), $voidTransaction);
        });
    }

    public function eligibleRewardTiers(int $balance, ?array $settings = null): array
    {
        $settings ??= $this->settings();

        return collect($settings['reward_tiers'] ?? [])
            ->filter(fn ($tier) => (bool) ($tier['active'] ?? true))
            ->filter(fn ($tier) => (int) ($tier['points_required'] ?? 0) > 0)
            ->filter(fn ($tier) => (int) $tier['points_required'] <= $balance)
            ->sortBy('points_required')
            ->values()
            ->all();
    }

    public function awardForCompletedOrder(Order $order): ?LoyaltyTransaction
    {
        if (! Schema::hasTable('loyalty_transactions')) {
            return null;
        }

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
            'reward_threshold' => max(0, (int) ($settings['reward_threshold'] ?? self::DEFAULT_SETTINGS['reward_threshold'])),
            'reward_tiers' => $this->normalizeRewardTiers($settings['reward_tiers'] ?? self::DEFAULT_SETTINGS['reward_tiers']),
            'redeem_value_per_point' => max(0, (float) ($settings['redeem_value_per_point'] ?? self::DEFAULT_SETTINGS['redeem_value_per_point'])),
            'earn_on_discounted_total' => (bool) ($settings['earn_on_discounted_total'] ?? self::DEFAULT_SETTINGS['earn_on_discounted_total']),
        ];
    }

    private function normalizeRewardTiers(mixed $tiers): array
    {
        if (! is_array($tiers)) {
            $tiers = self::DEFAULT_SETTINGS['reward_tiers'];
        }

        return collect($tiers)
            ->filter(fn ($tier) => is_array($tier))
            ->map(function (array $tier, int $index) {
                $points = max(0, (int) ($tier['points_required'] ?? 0));
                $id = trim((string) ($tier['id'] ?? ''));
                $normalizedId = $id !== '' ? Str::slug($id, '_') : 'reward_'.$points.'_'.$index;
                $defaultTier = collect(self::DEFAULT_SETTINGS['reward_tiers'])
                    ->first(fn ($item) => ($item['id'] ?? null) === $normalizedId || (int) ($item['points_required'] ?? 0) === $points);
                $applicationType = $tier['application_type'] ?? $defaultTier['application_type'] ?? 'fixed_discount';

                return [
                    'id' => $normalizedId,
                    'points_required' => $points,
                    'label' => trim((string) ($tier['label'] ?? "{$points} point reward")),
                    'reward_text' => trim((string) ($tier['reward_text'] ?? "Redeem {$points} points")),
                    'application_type' => in_array($applicationType, ['fixed_discount', 'free_product'], true)
                        ? $applicationType
                        : 'fixed_discount',
                    'discount_amount' => max(0, (float) ($tier['discount_amount'] ?? $defaultTier['discount_amount'] ?? 0)),
                    'product_id' => isset($tier['product_id']) ? (int) $tier['product_id'] : ($defaultTier['product_id'] ?? null),
                    'product_name' => trim((string) ($tier['product_name'] ?? $defaultTier['product_name'] ?? '')) ?: null,
                    'active' => (bool) ($tier['active'] ?? true),
                ];
            })
            ->filter(fn ($tier) => $tier['points_required'] > 0)
            ->sortBy('points_required')
            ->values()
            ->all();
    }

    private function generateRewardToken(): string
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $slug = $tenant?->slug ? Str::slug($tenant->slug, '_') : 'tenant';

        return $slug.'.'.Str::random(48);
    }

    private function hashRewardToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function normalizeRewardToken(string $token): string
    {
        $token = trim($token);

        if (str_starts_with($token, 'paychat-loyalty:')) {
            return substr($token, strlen('paychat-loyalty:'));
        }

        if (preg_match('~/loyalty/rewards/([^/?#]+)~', $token, $matches)) {
            return urldecode($matches[1]);
        }

        return $token;
    }

    private function redemptionResponse(Customer $customer, LoyaltyTransaction $transaction): array
    {
        $meta = $transaction->meta ?? [];

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'loyalty_points' => (int) $customer->loyalty_points,
                'total_visits' => (int) $customer->total_visits,
                'total_spend' => (float) $customer->total_spend,
                'last_visit_at' => optional($customer->last_visit_at)->toISOString(),
            ],
            'loyalty_balance' => (int) $customer->loyalty_points,
            'transaction' => [
                'id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
                'order_id' => $transaction->order_id,
                'type' => $transaction->type,
                'points' => (int) $transaction->points,
                'balance_after' => (int) $transaction->balance_after,
                'description' => $transaction->description,
                'meta' => $meta,
                'reward_tier' => $meta['reward_tier'] ?? null,
                'created_at' => optional($transaction->created_at)->toISOString(),
            ],
            'applied_reward' => [
                'type' => $meta['application_type'] ?? $meta['reward_tier']['application_type'] ?? 'fixed_discount',
                'discount_amount' => (float) ($meta['discount_amount'] ?? $meta['reward_tier']['discount_amount'] ?? 0),
                'product_id' => $meta['product_id'] ?? $meta['reward_tier']['product_id'] ?? null,
                'product_name' => $meta['product_name'] ?? $meta['reward_tier']['product_name'] ?? null,
            ],
        ];
    }

    private function maskPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        return str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4);
    }

    private function isDuplicateTransaction(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true);
    }
}
