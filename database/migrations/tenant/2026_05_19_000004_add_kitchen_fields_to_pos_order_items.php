<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_order_items', 'kitchen_status')) {
                $table->string('kitchen_status', 30)->nullable();
            }

            if (!Schema::hasColumn('pos_order_items', 'kitchen_batch_id')) {
                $table->unsignedBigInteger('kitchen_batch_id')->nullable();
            }

            if (!Schema::hasColumn('pos_order_items', 'sent_to_kitchen_at')) {
                $table->timestamp('sent_to_kitchen_at')->nullable();
            }

            if (!Schema::hasColumn('pos_order_items', 'item_status')) {
                $table->string('item_status', 30)->nullable();
            }
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->index('kitchen_batch_id', 'pos_order_items_kitchen_batch_id_index');
            $table->index('kitchen_status', 'pos_order_items_kitchen_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropIndex('pos_order_items_kitchen_batch_id_index');
            $table->dropIndex('pos_order_items_kitchen_status_index');
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('pos_order_items', 'item_status')) {
                $table->dropColumn('item_status');
            }

            if (Schema::hasColumn('pos_order_items', 'sent_to_kitchen_at')) {
                $table->dropColumn('sent_to_kitchen_at');
            }

            if (Schema::hasColumn('pos_order_items', 'kitchen_batch_id')) {
                $table->dropColumn('kitchen_batch_id');
            }

            if (Schema::hasColumn('pos_order_items', 'kitchen_status')) {
                $table->dropColumn('kitchen_status');
            }
        });
    }
};
