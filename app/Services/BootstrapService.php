<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;

class BootstrapService
{
    public function __construct(
        private TenantSettingsService $settings,
        private TenantFeatureService $features,
        private PermissionService $permissions,
    ) {
    }

    public function forUser(User $user, ?Tenant $tenant = null): array
    {
        $tenant = $tenant ?: $user->tenant;

        return [
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'slug' => $tenant->slug,
                'api_key' => $tenant->api_key,
                'industry' => $tenant->industry,
                'industry_type' => $this->features->industryType($tenant->industry),
            ] : null,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'features' => $tenant ? $this->features->forTenant($tenant) : [],
            'permissions' => $this->permissions->forUser($user),
            'settings' => $tenant ? $this->settings->grouped($tenant) : [],
        ];
    }
}
