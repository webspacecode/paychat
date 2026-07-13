<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Support\ModuleAccessResult;

class ModuleAccessService
{
    public function __construct(
        private ModuleEntitlementService $entitlements,
        private TenantFeatureService $features,
        private PermissionService $permissions,
    ) {
    }

    public function resolve(Tenant $tenant, User $user, string $moduleKey, ?string $permission = null): ModuleAccessResult
    {
        $module = config("modules.{$moduleKey}");
        $available = is_array($module) && (bool) ($module['available'] ?? false);
        $entitled = $available && $this->entitlements->isEntitled($tenant, $moduleKey);
        $enabled = $entitled && $this->features->has($tenant, $moduleKey);
        $basePermission = is_array($module) ? ($module['access_permission'] ?? null) : null;
        $permitted = $enabled
            && (! $basePermission || $this->permissions->has($user, $basePermission))
            && (! $permission || $permission === $basePermission || $this->permissions->has($user, $permission));

        $reason = match (true) {
            ! $available => 'unavailable',
            ! $entitled => 'not_entitled',
            ! $enabled => 'disabled',
            ! $permitted => 'permission_denied',
            default => 'allowed',
        };

        return new ModuleAccessResult($available, $entitled, $enabled, $permitted, $permitted, $reason);
    }

    public function ensureAccess(Tenant $tenant, User $user, string $moduleKey, ?string $permission = null): void
    {
        if (! $this->resolve($tenant, $user, $moduleKey, $permission)->allowed) {
            abort(403, 'Module unavailable.');
        }
    }

    public function publicStates(Tenant $tenant, User $user): array
    {
        return collect(config('modules', []))
            ->mapWithKeys(fn ($_module, $key) => [
                $key => $this->resolve($tenant, $user, (string) $key)->publicState(),
            ])
            ->all();
    }
}
