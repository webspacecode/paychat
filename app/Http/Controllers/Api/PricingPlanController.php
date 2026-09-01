<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaychatPricingPlan;
use Illuminate\Support\Facades\Schema;

class PricingPlanController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('paychat_pricing_plans')) {
            return response()->json(['data' => [
                [
                    'key' => 'trial',
                    'name' => 'Trial',
                    'description' => 'Default onboarding plan.',
                    'monthly_price' => 0,
                    'yearly_price' => 0,
                    'currency' => 'INR',
                    'trial_days' => 14,
                    'is_trial' => true,
                    'features' => [],
                ],
            ]]);
        }

        $plans = PaychatPricingPlan::with(['features' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
            ->get();

        return response()->json(['data' => $plans]);
    }
}
