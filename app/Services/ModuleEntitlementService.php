<?php

namespace App\Services;

use App\Models\Tenant;

class ModuleEntitlementService
{
    public function isEntitled(Tenant $tenant, string $moduleKey): bool
    {
        $module = config("modules.{$moduleKey}");

        if (! is_array($module)) {
            return false;
        }

        // No reliable plan-to-module mapping exists yet. This boundary is where
        // subscription/add-on enforcement will be added without changing callers.
        return ($module['entitlement_key'] ?? null) === null;
    }

    public function forget(Tenant $tenant, string $moduleKey): void
    {
        // Intentionally cache-free until a real entitlement source exists.
    }
}
