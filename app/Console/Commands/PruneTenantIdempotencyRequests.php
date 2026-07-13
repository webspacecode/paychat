<?php

namespace App\Console\Commands;

use App\Services\IdempotencyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PruneTenantIdempotencyRequests extends Command
{
    protected $signature = 'tenant:idempotency-prune {database : Direct tenant database name}';
    protected $description = 'Delete expired idempotency records from one tenant database';

    public function handle(IdempotencyService $idempotency): int
    {
        $database = (string) $this->argument('database');
        if (! preg_match('/^tenant_[A-Za-z0-9_]+$/', $database)) {
            $this->error('Invalid tenant database name.');
            return self::FAILURE;
        }

        $base = config('database.connections.mysql');
        Config::set('database.connections.tenant', array_merge($base, ['database' => $database]));
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        DB::reconnect('tenant');

        try {
            $count = $idempotency->pruneExpired();
            $this->info("Pruned {$count} expired idempotency request(s) from {$database}.");
            return self::SUCCESS;
        } finally {
            DB::purge('tenant');
        }
    }
}
