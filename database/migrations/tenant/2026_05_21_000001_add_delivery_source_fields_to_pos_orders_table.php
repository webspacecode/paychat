<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'delivery_channel')) {
                $table->string('delivery_channel', 50)->nullable();
            }

            if (!Schema::hasColumn('pos_orders', 'delivery_channel_label')) {
                $table->string('delivery_channel_label', 100)->nullable();
            }

            if (!Schema::hasColumn('pos_orders', 'external_order_reference')) {
                $table->string('external_order_reference', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'external_order_reference')) {
                $table->dropColumn('external_order_reference');
            }

            if (Schema::hasColumn('pos_orders', 'delivery_channel_label')) {
                $table->dropColumn('delivery_channel_label');
            }

            if (Schema::hasColumn('pos_orders', 'delivery_channel')) {
                $table->dropColumn('delivery_channel');
            }
        });
    }
};
