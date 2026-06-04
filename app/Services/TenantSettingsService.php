<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TenantSettingsService
{
    public function all(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?: $this->currentTenant();

        if (! $tenant) {
            return [];
        }

        return Cache::store('file')->remember($this->cacheKey($tenant), now()->addMinutes(10), function () use ($tenant) {
            return $this->readSettings($tenant);
        });
    }

    public function grouped(?Tenant $tenant = null): array
    {
        $settings = $this->all($tenant);

        return [
            'pos' => $this->onlyKeys($settings, [
                'business_date',
                'current_business_date',
                'shift_date',
                'business_day_start_time',
                'day_start_time',
            ]),
            'invoice' => $this->onlyKeys($settings, [
                'invoice_paper_size',
                'invoice_template',
                'invoice_auto_generate',
            ]),
            'tax' => $this->onlyKeys($settings, [
                'tax_mode',
                'gst_invoice',
            ]),
            'kitchen' => [
                'operation_mode' => $settings['kitchen_operation_mode'] ?? null,
            ],
            'token' => [
                'enabled' => $settings['token_system_enabled'] ?? null,
                'prefix' => $settings['token_prefix'] ?? null,
                'start_number' => $settings['token_start_number'] ?? null,
                'reset_daily' => $settings['token_reset_daily'] ?? null,
            ],
            'features' => $this->featureOverrides($settings),
            'raw' => $settings,
        ];
    }

    public function featureOverrides(mixed $tenantOrNull = null): array
    {
        $settings = is_array($tenantOrNull) ? $tenantOrNull : $this->all($tenantOrNull);
        $features = [];

        foreach (['features', 'features.enabled', 'enabled_features'] as $key) {
            if (isset($settings[$key]) && is_array($settings[$key])) {
                $features = array_merge($features, $settings[$key]);
            }
        }

        foreach ($settings as $key => $value) {
            if (! str_starts_with((string) $key, 'features.')) {
                continue;
            }

            $feature = substr((string) $key, strlen('features.'));

            if ($feature !== '' && $feature !== 'enabled') {
                $features[$feature] = (bool) $value;
            }
        }

        return collect($features)
            ->map(fn ($value) => (bool) $value)
            ->all();
    }

    public function forget(?Tenant $tenant = null): void
    {
        $tenant = $tenant ?: $this->currentTenant();

        if ($tenant) {
            Cache::store('file')->forget($this->cacheKey($tenant));
        }
    }

    private function readSettings(Tenant $tenant): array
    {
        try {
            $this->configureTenantConnection($tenant);

            if (! Schema::connection('tenant')->hasTable('settings')) {
                return [];
            }

            return DB::connection('tenant')
                ->table('settings')
                ->get(['setting_key', 'value', 'type'])
                ->mapWithKeys(fn ($row) => [
                    $row->setting_key => $this->castValue($row->value, $row->type),
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function configureTenantConnection(Tenant $tenant): void
    {
        $base = config('database.connections.mysql');

        Config::set('database.connections.tenant', array_merge($base, [
            'database' => $tenant->database,
        ]));

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function castValue($value, ?string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => is_string($value) ? (json_decode($value, true) ?? []) : [],
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    private function onlyKeys(array $settings, array $keys): array
    {
        return collect($settings)
            ->only($keys)
            ->filter(fn ($value) => $value !== null)
            ->all();
    }

    private function cacheKey(Tenant $tenant): string
    {
        return "tenant:{$tenant->id}:settings:v1";
    }

    private function currentTenant(): ?Tenant
    {
        return app()->bound('currentTenant') ? app('currentTenant') : null;
    }
}
