<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Tenant\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModuleSettingsService
{
    public function __construct(
        private TenantSettingsService $settings,
        private TenantFeatureService $features,
        private ModuleEntitlementService $entitlements,
    ) {
    }

    public function setEnabled(Tenant $tenant, string $moduleKey, bool $enabled): array
    {
        $module = config("modules.{$moduleKey}");
        if (! is_array($module)) {
            throw ValidationException::withMessages(['module' => 'Unknown module.']);
        }
        if (! ($module['available'] ?? false)) {
            abort(403, 'Module unavailable.');
        }
        if ($enabled && ! $this->entitlements->isEntitled($tenant, $moduleKey)) {
            abort(403, 'Module unavailable.');
        }

        DB::connection('tenant')->transaction(function () use ($moduleKey, $enabled) {
            Setting::set("features.{$moduleKey}", $enabled, 'boolean');
        });

        $this->forget($tenant, $moduleKey);

        return ['module' => $moduleKey, 'enabled' => $this->features->has($tenant, $moduleKey)];
    }

    public function updateRegistrationSettings(Tenant $tenant, array $values): array
    {
        $definitions = config('modules.registration_management.settings', []);

        DB::connection('tenant')->transaction(function () use ($values, $definitions) {
            foreach ($values as $key => $value) {
                $definition = $definitions[$key];
                Setting::set("registration.{$key}", $value, $definition['type']);
            }
        });

        $this->forget($tenant, 'registration_management');

        return $this->settings->registrationSettings($tenant);
    }

    public function forget(Tenant $tenant, string $moduleKey): void
    {
        $this->settings->forget($tenant);
        $this->features->forget($tenant);
        $this->entitlements->forget($tenant, $moduleKey);
    }
}
