<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class TenantOperationalLogReader
{
    public function read(Tenant $tenant, array $filters = []): array
    {
        $date = $this->date($filters['date'] ?? null);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($filters['per_page'] ?? 25)));

        return $this->readPaths([$this->path($tenant, $date)], $filters, $date, $page, $perPage);
    }

    public function readSystem(array $filters = []): array
    {
        $date = $this->date($filters['date'] ?? null);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($filters['per_page'] ?? 25)));

        return $this->readPaths([
            $this->bucketPath('system', $date),
            $this->bucketPath('unknown', $date),
        ], $filters, $date, $page, $perPage);
    }

    private function readPaths(array $paths, array $filters, string $date, int $page, int $perPage): array
    {
        $rows = [];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            $rows = array_merge($rows, $this->readRows($path));
        }

        if ($rows === []) {
            return $this->empty($date, $page, $perPage);
        }

        usort($rows, fn ($a, $b) => (int) ($b['logged_at_unix'] ?? 0) <=> (int) ($a['logged_at_unix'] ?? 0));
        $rows = array_values(array_filter($rows, fn ($row) => $this->matches($row, $filters)));

        $total = count($rows);
        $data = array_slice($rows, ($page - 1) * $perPage, $perPage);

        return [
            'data' => $data,
            'meta' => [
                'date' => $date,
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }

    public function availableDates(Tenant $tenant, int $limit = 30): array
    {
        $dir = storage_path('logs/tenant-errors/tenant-'.$tenant->id);

        return $this->datesFromDirectories([$dir], $limit);
    }

    public function availableSystemDates(int $limit = 30): array
    {
        return $this->datesFromDirectories([
            storage_path('logs/tenant-errors/system'),
            storage_path('logs/tenant-errors/unknown'),
        ], $limit);
    }

    private function datesFromDirectories(array $directories, int $limit): array
    {
        return collect($directories)
            ->filter(fn ($dir) => File::isDirectory($dir))
            ->flatMap(fn ($dir) => File::files($dir))
            ->map(fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME))
            ->filter(fn ($date) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
            ->unique()
            ->sortDesc()
            ->take($limit)
            ->values()
            ->all();
    }

    private function readRows(string $path): array
    {
        $maxLines = max(1, (int) config('observability.tenant_operational_logs_max_read_lines', 2000));
        $lines = $this->tail($path, $maxLines);
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $row = json_decode($line, true);
            if (! is_array($row)) {
                continue;
            }

            $rows[] = $this->safeRow($row);
        }

        return $rows;
    }

    private function tail(string $path, int $maxLines): array
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        $start = max(0, $lastLine - $maxLines + 1);
        $lines = [];

        for ($line = $start; $line <= $lastLine; $line++) {
            $file->seek($line);
            $value = trim((string) $file->current());

            if ($value !== '') {
                $lines[] = $value;
            }
        }

        return $lines;
    }

    private function matches(array $row, array $filters): bool
    {
        foreach (['module', 'level', 'severity', 'event'] as $field) {
            if (! empty($filters[$field]) && ($row[$field] ?? null) !== $filters[$field]) {
                return false;
            }
        }

        if (! empty($filters['support_code'])) {
            $needle = strtolower((string) $filters['support_code']);
            $value = strtolower((string) ($row['support_code'] ?? ''));

            if (! str_contains($value, $needle)) {
                return false;
            }
        }

        return true;
    }

    private function safeRow(array $row): array
    {
        return array_intersect_key($row, array_flip([
            'timestamp',
            'logged_at_unix',
            'level',
            'severity',
            'event',
            'module',
            'support_code',
            'request_id',
            'tenant_id',
            'tenant_slug',
            'location_id',
            'user_id',
            'method',
            'path',
            'route',
            'status_code',
            'safe_message',
            'exception_class',
            'exception_message',
            'file',
            'line',
        ]));
    }

    private function date(?string $date): string
    {
        if (! $date) {
            return Carbon::now(config('app.timezone'))->toDateString();
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw ValidationException::withMessages([
                'date' => 'Date must be in YYYY-MM-DD format.',
            ]);
        }

        return $date;
    }

    private function path(Tenant $tenant, string $date): string
    {
        return $this->bucketPath('tenant-'.$tenant->id, $date);
    }

    private function bucketPath(string $bucket, string $date): string
    {
        return storage_path('logs/tenant-errors/'.$bucket.'/'.$date.'.log');
    }

    private function empty(string $date, int $page, int $perPage): array
    {
        return [
            'data' => [],
            'meta' => [
                'date' => $date,
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'last_page' => 1,
            ],
        ];
    }
}
