<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\OperationalLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckTenantOperationalLogs extends Command
{
    protected $signature = 'ops:tenant-logs-check
        {tenant? : Tenant id or slug to inspect}
        {--write : Write one safe test event for the selected real tenant}';

    protected $description = 'Inspect tenant operational JSONL log configuration and optionally write a safe test event.';

    public function handle(OperationalLogService $logs): int
    {
        $tenant = $this->tenant();
        $date = Carbon::now(config('app.timezone'))->toDateString();
        $bucket = $tenant ? 'tenant-'.$tenant->id : 'system';
        $path = storage_path("logs/tenant-errors/{$bucket}/{$date}.log");

        $this->line('Tenant operational logs: '.(config('observability.tenant_operational_logs_enabled', true) ? 'enabled' : 'disabled'));
        $this->line('App timezone: '.config('app.timezone'));
        $this->line('Storage logs directory: '.storage_path('logs'));
        $this->line('Storage logs writable: '.(is_writable(storage_path('logs')) ? 'yes' : 'no'));
        $this->line('Target bucket: '.$bucket);
        $this->line('Expected file: '.$path);
        $this->line('File exists: '.(is_file($path) ? 'yes' : 'no'));

        if ($this->argument('tenant') && ! $tenant) {
            $this->error('Tenant not found for id/slug: '.$this->argument('tenant'));
            return self::FAILURE;
        }

        if (! $this->option('write')) {
            $this->info('Run with --write and a real tenant id/slug to write a safe diagnostic row.');
            return self::SUCCESS;
        }

        if (! $tenant) {
            $this->error('Please pass a real tenant id or slug when using --write.');
            return self::FAILURE;
        }

        $supportCode = 'OPS-CHECK-'.now()->format('YmdHis');

        $logs->write('warning', 'kds.consistency.batch_missing_table_display', [
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'request_id' => $supportCode,
            'support_code' => $supportCode,
            'module' => 'kds',
            'severity' => 'low',
            'status_code' => 200,
            'safe_message' => 'Tenant operational log diagnostic row',
        ]);

        clearstatcache(true, $path);

        if (! is_file($path)) {
            $this->error('Diagnostic write did not create the expected file.');
            return self::FAILURE;
        }

        $this->info('Diagnostic row written. Search support_code: '.$supportCode);
        return self::SUCCESS;
    }

    private function tenant(): ?Tenant
    {
        $value = $this->argument('tenant');

        if (! $value) {
            return null;
        }

        return Tenant::query()
            ->when(is_numeric($value), fn ($query) => $query->whereKey((int) $value), fn ($query) => $query->where('slug', $value))
            ->first();
    }
}
