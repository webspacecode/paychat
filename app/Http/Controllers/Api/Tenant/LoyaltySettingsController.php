<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Services\LoyaltyService;
use App\Services\TenantSettingsService;
use Illuminate\Http\Request;

class LoyaltySettingsController extends Controller
{
    public function show(LoyaltyService $loyalty)
    {
        return response()->json($loyalty->settings());
    }

    public function update(Request $request, LoyaltyService $loyalty)
    {
        $validated = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'points_per_100' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'minimum_redemption' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'reward_threshold' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'reward_tiers' => ['sometimes', 'array'],
            'reward_tiers.*.id' => ['nullable', 'string', 'max:80'],
            'reward_tiers.*.points_required' => ['required_with:reward_tiers', 'integer', 'min:1', 'max:1000000'],
            'reward_tiers.*.label' => ['required_with:reward_tiers', 'string', 'max:120'],
            'reward_tiers.*.reward_text' => ['required_with:reward_tiers', 'string', 'max:255'],
            'reward_tiers.*.application_type' => ['sometimes', 'string', 'in:fixed_discount,free_product'],
            'reward_tiers.*.discount_amount' => ['sometimes', 'numeric', 'min:0', 'max:1000000'],
            'reward_tiers.*.product_id' => ['nullable', 'integer', 'min:1'],
            'reward_tiers.*.product_name' => ['nullable', 'string', 'max:160'],
            'reward_tiers.*.active' => ['sometimes', 'boolean'],
            'redeem_value_per_point' => ['sometimes', 'numeric', 'min:0', 'max:1000000'],
            'earn_on_discounted_total' => ['sometimes', 'boolean'],
        ]);

        $settings = $loyalty->updateSettings($validated);

        app(TenantSettingsService::class)->forget();

        return response()->json($settings);
    }
}
