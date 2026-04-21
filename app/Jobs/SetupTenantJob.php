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

    public function __construct($tenant, $dbName)
    {
        $this->tenant = $tenant;
        $this->dbName = $dbName;
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
    }
}
