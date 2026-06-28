<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_payments', 'collected_by')) {
                $table->unsignedBigInteger('collected_by')->nullable()->after('status');
            }
        });

        $this->addIndexIfMissing('pos_payments', 'pos_payments_collected_by_index', ['collected_by']);
        $this->addIndexIfMissing('pos_payments', 'pos_payments_status_created_at_index', ['status', 'created_at']);
        $this->addIndexIfMissing('pos_payments', 'pos_payments_method_status_created_index', ['payment_method', 'status', 'created_at']);

        if (Schema::hasColumn('pos_orders', 'created_by')) {
            $this->addIndexIfMissing('pos_orders', 'pos_orders_created_by_index', ['created_by']);
        }

        if (Schema::hasColumn('pos_orders', 'completed_by')) {
            $this->addIndexIfMissing('pos_orders', 'pos_orders_completed_by_index', ['completed_by']);
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('pos_orders', 'pos_orders_completed_by_index');
        $this->dropIndexIfExists('pos_orders', 'pos_orders_created_by_index');
        $this->dropIndexIfExists('pos_payments', 'pos_payments_method_status_created_index');
        $this->dropIndexIfExists('pos_payments', 'pos_payments_status_created_at_index');
        $this->dropIndexIfExists('pos_payments', 'pos_payments_collected_by_index');

        Schema::table('pos_payments', function (Blueprint $table) {
            if (Schema::hasColumn('pos_payments', 'collected_by')) {
                $table->dropColumn('collected_by');
            }
        });
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($index, $columns) {
            $tableBlueprint->index($columns, $index);
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! $this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($index) {
            $tableBlueprint->dropIndex($index);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

        return count($rows) > 0;
    }
};
