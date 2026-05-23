<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Support\IndustryNormalizer;
use Throwable;

class SetupTenantJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $tenant;
    protected $dbName;
    public $setupData;

    public function __construct($tenant, $dbName, $setupData = [])
    {
        $this->tenant = $tenant;
        $this->dbName = $dbName;
        $this->setupData = $setupData;
    }

    public function handle()
    {
        $this->markOnboarding('provisioning', [
            'failed_reason' => null,
            'setup_started_at' => now(),
            'setup_completed_at' => null,
        ]);

        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$this->dbName}`");

            $base = config('database.connections.mysql');

            Config::set('database.connections.tenant', array_merge($base, [
                'database' => $this->dbName,
            ]));

            DB::purge('tenant');
            DB::reconnect('tenant');

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => base_path('database/migrations/tenant'),
                '--realpath' => true,
                '--force' => true,
            ]);

            $this->seedDefaultLocation();
            $this->seedTaxConfig();
            $this->seedBranding();
            $this->seedPaymentMethods();
            $this->seedDefaultServiceCategory();
            $this->seedSettings();

            $this->markOnboarding('ready', [
                'failed_reason' => null,
                'setup_completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $safeMessage = substr($e->getMessage(), 0, 1000);

            $this->markOnboarding('failed', [
                'failed_reason' => $safeMessage,
            ]);

            Log::error('Tenant setup failed', [
                'tenant_id' => $this->tenant->id ?? null,
                'tenant_slug' => $this->tenant->slug ?? null,
                'database' => $this->dbName,
                'exception_class' => get_class($e),
                'exception_message' => $safeMessage,
            ]);

            throw $e;
        }
    }

    private function markOnboarding(string $status, array $values = []): void
    {
        DB::connection('mysql')->table('tenant_onboardings')->updateOrInsert(
            ['tenant_id' => $this->tenant->id],
            array_merge($values, [
                'status' => $status,
                'updated_at' => now(),
                'created_at' => now(),
            ])
        );
    }

    private function seedDefaultLocation(): void
    {
        DB::connection('tenant')->table('locations')->updateOrInsert(
            [
                'name' => $this->tenant->slug,
                'type' => 'default',
            ],
            [
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function seedTaxConfig(): void
    {
        DB::connection('mysql')->table('tax_configs')->updateOrInsert(
            ['tenant_id' => $this->tenant->id],
            [
                'gst_number' => $this->setupData['gst_number'] ?? null,
                'is_gst_enabled' => $this->setupData['is_gst_enabled'] ?? 0,
                'is_inclusive' => 0,
                'cgst_rate' => 9,
                'sgst_rate' => 9,
                'igst_rate' => 18,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function seedBranding(): void
    {
        DB::connection('mysql')->table('brandings')->updateOrInsert(
            ['tenant_id' => $this->tenant->id],
            [
                'company_name' => $this->tenant->name,
                'logo' => $this->setupData['logo'] ?? null,
                'primary_color' => '#4F46E5',
                'phone' => $this->setupData['phone'] ?? null,
                'address' => $this->setupData['address'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function seedPaymentMethods(): void
    {
        $methods = [
            [
                'type' => 'cash',
                'mode' => null,
                'enabled' => 1,
                'config' => null,
            ],
            [
                'type' => 'upi',
                'mode' => 'personal',
                'enabled' => 1,
                'config' => json_encode([
                    'upi_id' => $this->setupData['upi_id'] ?? '',
                    'name' => $this->tenant->name,
                ]),
            ],
            [
                'type' => 'upi',
                'mode' => 'business',
                'enabled' => 0,
                'config' => json_encode([
                    'provider' => 'phonepe',
                    'merchant_id' => '',
                ]),
            ],
            [
                'type' => 'gateway',
                'mode' => null,
                'enabled' => 0,
                'config' => json_encode([
                    'provider' => 'razorpay',
                    'key' => '',
                    'secret' => '',
                ]),
            ],
        ];

        foreach ($methods as $method) {
            DB::connection('tenant')->table('payment_methods')->updateOrInsert(
                [
                    'type' => $method['type'],
                    'mode' => $method['mode'],
                ],
                [
                    'enabled' => $method['enabled'],
                    'config' => $method['config'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedSettings(): void
    {
        $enableTokenSystem = array_key_exists('enable_token_system', $this->setupData)
            ? (bool) $this->setupData['enable_token_system']
            : ! IndustryNormalizer::isSimpleBilling($this->tenant->industry ?? null);

        $settings = [
            [
                'setting_key' => 'token_system_enabled',
                'value' => $enableTokenSystem ? 'true' : 'false',
                'type' => 'boolean',
            ],
            [
                'setting_key' => 'token_prefix',
                'value' => 'A',
                'type' => 'string',
            ],
            [
                'setting_key' => 'token_start_number',
                'value' => '100',
                'type' => 'string',
            ],
            [
                'setting_key' => 'token_reset_daily',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'setting_key' => 'kitchen_operation_mode',
                'value' => IndustryNormalizer::isSimpleBilling($this->tenant->industry ?? null)
                    ? 'inline'
                    : 'dedicated_kds',
                'type' => 'string',
            ],
        ];

        foreach ($settings as $setting) {
            DB::connection('tenant')->table('settings')->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedDefaultServiceCategory(): void
    {
        if (! IndustryNormalizer::isSimpleBilling($this->tenant->industry ?? null)) {
            return;
        }

        DB::connection('tenant')->table('categories')->updateOrInsert(
            ['name' => 'Services'],
            [
                'description' => 'Services',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
