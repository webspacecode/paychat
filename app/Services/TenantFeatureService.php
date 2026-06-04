<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantFeatureService
{
    public function __construct(private TenantSettingsService $settings)
    {
    }

    public function industryType(?string $industry): string
    {
        $industry = strtolower(trim((string) $industry));
        $aliases = config('industries.aliases', []);
        $default = config('industries.default', 'general');

        return $aliases[$industry] ?? $default;
    }

    public function defaultsForIndustry(?string $industry): array
    {
        $industryType = $this->industryType($industry);
        $defaults = config("industries.features.{$industryType}", []);

        if (!$defaults) {
            return config('industries.features.general', []);
        }

        return $defaults;
    }

    public function forTenant(Tenant $tenant): array
    {
        return Cache::store('file')->remember($this->cacheKey($tenant), now()->addMinutes(10), function () use ($tenant) {
            $defaults = $this->defaultsForIndustry($tenant->industry);
            $overrides = $this->settings->featureOverrides($tenant);

            return array_merge($defaults, array_intersect_key($overrides, $defaults));
        });
    }

    public function has(Tenant $tenant, string $feature): bool
    {
        return (bool) ($this->forTenant($tenant)[$feature] ?? false);
    }

    public function forget(Tenant $tenant): void
    {
        Cache::store('file')->forget($this->cacheKey($tenant));
    }

    private function cacheKey(Tenant $tenant): string
    {
        return "tenant:{$tenant->id}:features:v1";
    }
}
