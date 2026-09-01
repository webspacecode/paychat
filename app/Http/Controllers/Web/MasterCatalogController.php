<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaychatFeature;
use App\Models\PaychatPricingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterCatalogController extends Controller
{
    public function features(): View
    {
        return view('master.features', [
            'features' => PaychatFeature::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function storeFeature(Request $request): RedirectResponse
    {
        PaychatFeature::create($this->validateFeature($request));

        return back()->with('status', 'Feature added.');
    }

    public function updateFeature(Request $request, PaychatFeature $feature): RedirectResponse
    {
        $feature->update($this->validateFeature($request, $feature));

        return back()->with('status', 'Feature updated.');
    }

    public function plans(): View
    {
        return view('master.plans', [
            'features' => PaychatFeature::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
            'plans' => PaychatPricingPlan::with('features')->orderBy('sort_order')->orderBy('monthly_price')->get(),
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $this->validatePlan($request);
        $featureIds = $validated['feature_ids'] ?? [];
        unset($validated['feature_ids']);

        $plan = PaychatPricingPlan::create($validated);
        $plan->features()->sync($featureIds);

        return back()->with('status', 'Plan added.');
    }

    public function updatePlan(Request $request, PaychatPricingPlan $plan): RedirectResponse
    {
        $validated = $this->validatePlan($request, $plan);
        $featureIds = $validated['feature_ids'] ?? [];
        unset($validated['feature_ids']);

        $plan->update($validated);
        $plan->features()->sync($featureIds);

        return back()->with('status', 'Plan updated.');
    }

    private function validateFeature(Request $request, ?PaychatFeature $feature = null): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/', Rule::unique('paychat_features', 'key')->ignore($feature)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:80'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'source' => $request->input('source', 'master'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function validatePlan(Request $request, ?PaychatPricingPlan $plan = null): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/', Rule::unique('paychat_pricing_plans', 'key')->ignore($plan)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'yearly_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['required', 'string', 'size:3'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'is_trial' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:paychat_features,id'],
        ]) + [
            'trial_days' => (int) $request->input('trial_days', 0),
            'is_trial' => $request->boolean('is_trial'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
