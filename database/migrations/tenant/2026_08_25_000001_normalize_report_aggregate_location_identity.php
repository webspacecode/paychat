<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ALL_LOCATIONS_ID = 0;

    private array $uniqueIndexes = [
        'report_payment_breakdowns' => [
            'name' => 'report_payments_unique_identity',
            'columns' => ['tenant_id', 'location_id', 'date', 'payment_method'],
        ],
        'report_top_products_daily' => [
            'name' => 'report_top_products_unique_identity',
            'columns' => ['tenant_id', 'location_id', 'date', 'product_id'],
        ],
        'report_hourly_sales' => [
            'name' => 'report_hourly_sales_unique_identity',
            'columns' => ['tenant_id', 'location_id', 'date', 'hour'],
        ],
    ];

    public function up(): void
    {
        $this->normalizeAggregateTable('report_daily_sales', []);
        $this->normalizeAggregateTable('report_kpi_summaries', []);
        $this->normalizeAggregateTable('report_payment_breakdowns', ['payment_method']);
        $this->normalizeAggregateTable('report_top_products_daily', ['product_id']);
        $this->normalizeAggregateTable('report_hourly_sales', ['hour']);

        foreach ($this->uniqueIndexes as $table => $index) {
            if (Schema::hasTable($table) && ! $this->indexExists($table, $index['name'])) {
                Schema::table($table, function ($blueprint) use ($index) {
                    $blueprint->unique($index['columns'], $index['name']);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->uniqueIndexes as $table => $index) {
            if (Schema::hasTable($table) && $this->indexExists($table, $index['name'])) {
                Schema::table($table, function ($blueprint) use ($index) {
                    $blueprint->dropUnique($index['name']);
                });
            }
        }
    }

    private function normalizeAggregateTable(string $table, array $dimensions): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = array_merge(['id', 'tenant_id', 'location_id', 'date', 'updated_at'], $dimensions);
        $rows = DB::table($table)
            ->select($columns)
            ->orderBy('id')
            ->get();

        $groups = [];

        foreach ($rows as $row) {
            $keyParts = [
                $row->tenant_id,
                $row->location_id ?? self::ALL_LOCATIONS_ID,
                $row->date,
            ];

            foreach ($dimensions as $dimension) {
                $keyParts[] = $row->{$dimension};
            }

            $groups[implode('|', $keyParts)][] = $row;
        }

        foreach ($groups as $duplicates) {
            if (count($duplicates) < 2) {
                continue;
            }

            usort($duplicates, function ($a, $b) {
                $updated = strcmp((string) ($b->updated_at ?? ''), (string) ($a->updated_at ?? ''));

                return $updated !== 0 ? $updated : ((int) $b->id <=> (int) $a->id);
            });

            $deleteIds = collect(array_slice($duplicates, 1))->pluck('id')->all();

            if ($deleteIds) {
                DB::table($table)->whereIn('id', $deleteIds)->delete();
            }
        }

        DB::table($table)
            ->whereNull('location_id')
            ->update(['location_id' => self::ALL_LOCATIONS_ID]);
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return DB::selectOne("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?", [$table, $index]) !== null;
        }

        if ($driver === 'mysql') {
            $safeTable = str_replace('`', '``', $table);

            return DB::selectOne("SHOW INDEX FROM `{$safeTable}` WHERE Key_name = ?", [$index]) !== null;
        }

        return false;
    }
};
