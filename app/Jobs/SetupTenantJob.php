<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
        // Setup tenant DB connection

        $base = config('database.connections.mysql');
        
        Config::set('database.connections.tenant', array_merge($base, [
            'database' => $this->dbName,
        ]));

        DB::purge('tenant');
        DB::reconnect('tenant');

        // Run migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => base_path('database/migrations/tenant'),
            '--realpath' => true,
            '--force' => true,
        ]);
        
        // Default location
        DB::connection('tenant')->table('locations')->insert([
            'name' => $this->tenant->slug,
            'type' => 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('mysql')->table('tax_configs')->insert([
            'tenant_id' => $this->tenant->id,
            'gst_number' => $this->setupData['gst_number'] ?? null,
            'is_gst_enabled' => $this->setupData['is_gst_enabled'] ?? 0,
            'is_inclusive' => 0,
            'cgst_rate' => 9,
            'sgst_rate' => 9,
            'igst_rate' => 18,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('mysql')->table('brandings')->insert([
            'tenant_id' => $this->tenant->id,
            'company_name' => $this->tenant->name,
            'logo' => $this->setupData['logo'] ?? null,
            'primary_color' => '#4F46E5',
            'phone' => $this->setupData['phone'] ?? null,
            'address' => $this->setupData['address'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('payment_methods')->insert([
            [
                'type' => 'cash',
                'mode' => null,
                'enabled' => 1,
                'config' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'type' => 'upi',
                'mode' => 'personal',
                'enabled' => 1,
                'config' => json_encode([
                    'upi_id' => $this->setupData['upi_id'] ?? '',
                    'name' => $this->tenant->name,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'type' => 'upi',
                'mode' => 'business',
                'enabled' => 0,
                'config' => json_encode([
                    'provider' => 'phonepe',
                    'merchant_id' => '',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::connection('tenant')->table('settings')->insert([
            [
                'setting_key' => 'token_system_enabled',
                'value' => ($this->setupData['enable_token_system'] ?? true) ? 'true' : 'false',
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
        ]);
    }
}
