<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MigrateTenantDatabase extends Command
{
    protected $signature = 'tenant:migrate-db
        {database : Direct tenant database name, e.g. tenant_frozen_cafe}
        {--path=database/migrations/tenant : Migration path or specific migration file}
        {--pretend : Dump SQL queries without running}
        {--step : Force migrations to be run so they can be rolled back individually}';

    protected $description = 'Run tenant migrations on one direct tenant database name';

    protected $help = <<<'HELP'
Examples:
  php artisan tenant:migrate-db tenant_frozen_cafe --path=database/migrations/tenant
  php artisan tenant:migrate-db tenant_frozen_cafe --path=database/migrations/tenant/2026_05_22_000000_create_upi_profiles_table.php
  php artisan tenant:migrate-db tenant_frozen_cafe --path=database/migrations/tenant --pretend
HELP;

    public function handle(): int
    {
        $database = (string) $this->argument('database');
        $path = (string) $this->option('path');

        if (! $this->validDatabaseName($database)) {
            $this->error('Invalid database name. Use only tenant_ followed by letters, numbers, and underscores.');

            return self::FAILURE;
        }

        try {
            $migrationPath = $this->validatedMigrationPath($path);

            if (! $migrationPath) {
                $this->error('Invalid migration path. Use database/migrations/tenant or a file inside that folder.');

                return self::FAILURE;
            }

            if (! $this->databaseExists($database)) {
                $this->error("Database {$database} does not exist.");

                return self::FAILURE;
            }

            $this->configureTenantConnection($database);

            $selected = DB::connection('tenant')->getDatabaseName();

            if ($selected !== $database) {
                $this->error("Tenant connection selected {$selected}, expected {$database}.");

                return self::FAILURE;
            }

            $this->line("Selected database: {$database}");
            $this->line("Migration path: {$migrationPath['display']}");

            if (app()->environment('production')) {
                $confirmed = $this->confirm(
                    "You are about to run tenant migrations on database: {$database} using path: {$migrationPath['display']}. Continue?"
                );

                if (! $confirmed) {
                    $this->warn('Migration aborted. No migrations were run.');

                    return self::SUCCESS;
                }
            }

            $exitCode = Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => $migrationPath['real'],
                '--realpath' => true,
                '--force' => app()->environment('production'),
                '--pretend' => (bool) $this->option('pretend'),
                '--step' => (bool) $this->option('step'),
            ]);

            $output = trim(Artisan::output());

            if ($output !== '') {
                $this->line($output);
            }

            if ($exitCode !== self::SUCCESS) {
                $this->error("Tenant migration failed with exit code {$exitCode}.");

                return self::FAILURE;
            }

            $this->info("Tenant migrations completed for {$database}.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('Tenant migrate-db command failed', [
                'command' => 'tenant:migrate-db',
                'database' => $database,
                'path' => $path,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            $this->error('Tenant migration failed: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            DB::purge('tenant');
        }
    }

    private function validDatabaseName(string $database): bool
    {
        return (bool) preg_match('/^tenant_[A-Za-z0-9_]+$/', $database);
    }

    private function databaseExists(string $database): bool
    {
        return DB::connection('mysql')
            ->selectOne(
                'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
                [$database]
            ) !== null;
    }

    private function validatedMigrationPath(string $path): ?array
    {
        if (str_contains($path, "\0") || str_contains($path, '..')) {
            return null;
        }

        $tenantMigrationsRoot = realpath(base_path('database/migrations/tenant'));

        if (! $tenantMigrationsRoot) {
            return null;
        }

        $candidate = str_starts_with($path, DIRECTORY_SEPARATOR)
            ? $path
            : base_path($path);

        $realCandidate = realpath($candidate);

        if (! $realCandidate) {
            return null;
        }

        $root = rtrim($tenantMigrationsRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $insideRoot = $realCandidate === $tenantMigrationsRoot
            || str_starts_with($realCandidate, $root);

        if (! $insideRoot) {
            return null;
        }

        if (is_file($realCandidate) && pathinfo($realCandidate, PATHINFO_EXTENSION) !== 'php') {
            return null;
        }

        return [
            'real' => $realCandidate,
            'display' => $this->relativePath($realCandidate),
        ];
    }

    private function configureTenantConnection(string $database): void
    {
        $base = config('database.connections.tenant') ?: config('database.connections.mysql');

        Config::set('database.connections.tenant', array_merge($base, [
            'database' => $database,
        ]));

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function relativePath(string $path): string
    {
        $base = rtrim(base_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($path, $base)
            ? substr($path, strlen($base))
            : $path;
    }
}
