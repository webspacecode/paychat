<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'dining_flow')) {
                $table->string('dining_flow', 50)->nullable()->after('order_type');
            }

            if (!Schema::hasColumn('pos_orders', 'table_session_id')) {
                $table->unsignedBigInteger('table_session_id')->nullable()->after('table_id');
            }

            if (!Schema::hasColumn('pos_orders', 'guest_count')) {
                $table->unsignedInteger('guest_count')->nullable()->after('table_session_id');
            }
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->index('table_id', 'pos_orders_table_id_index');
            $table->index('table_session_id', 'pos_orders_table_session_id_index');
            $table->index('dining_flow', 'pos_orders_dining_flow_index');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropIndex('pos_orders_table_id_index');
            $table->dropIndex('pos_orders_table_session_id_index');
            $table->dropIndex('pos_orders_dining_flow_index');
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'guest_count')) {
                $table->dropColumn('guest_count');
            }

            if (Schema::hasColumn('pos_orders', 'table_session_id')) {
                $table->dropColumn('table_session_id');
            }

            if (Schema::hasColumn('pos_orders', 'dining_flow')) {
                $table->dropColumn('dining_flow');
            }
        });
    }
};
