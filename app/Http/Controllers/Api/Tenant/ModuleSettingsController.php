<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateRegistrationSettingsRequest;
use App\Services\ModuleAccessService;
use App\Services\ModuleSettingsService;
use App\Services\TenantSettingsService;
use App\Support\Observability;
use Illuminate\Http\Request;

class ModuleSettingsController extends Controller
{
    public function updateEnabled(string $tenantSlug, string $module, Request $request, ModuleSettingsService $settings, ModuleAccessService $access)
    {
        $validated = $request->validate(['enabled' => ['required', 'boolean']]);
        $tenant = app('currentTenant');
        $state = $settings->setEnabled($tenant, $module, (bool) $validated['enabled']);

        Observability::logInfo('module.'.($state['enabled'] ? 'enabled' : 'disabled'), [
            'tenant_id' => $tenant->id,
            'user_id' => $request->user()->id,
            'module' => $module,
        ], $request);

        return response()->json([
            ...$state,
            'state' => $access->resolve($tenant, $request->user(), $module)->publicState(),
        ]);
    }

    public function registration(string $tenantSlug, TenantSettingsService $settings)
    {
        return response()->json(['data' => $settings->registrationSettings(app('currentTenant'))]);
    }

    public function updateRegistration(string $tenantSlug, UpdateRegistrationSettingsRequest $request, ModuleSettingsService $settings)
    {
        $values = $settings->updateRegistrationSettings(app('currentTenant'), $request->validated());
        return response()->json(['data' => $values]);
    }

    public function access(): array
    {
        return ['accessible' => true];
    }
}
