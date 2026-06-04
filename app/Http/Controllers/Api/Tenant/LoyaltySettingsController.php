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
            'redeem_value_per_point' => ['sometimes', 'numeric', 'min:0', 'max:1000000'],
            'earn_on_discounted_total' => ['sometimes', 'boolean'],
        ]);

        $settings = $loyalty->updateSettings($validated);

        app(TenantSettingsService::class)->forget();

        return response()->json($settings);
    }
}
