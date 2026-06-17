<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bakery_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('bakery_orders', 'order_type')) {
                $table->string('order_type', 50)->default('custom_cake')->after('customer_phone');
            }

            if (! Schema::hasColumn('bakery_orders', 'cake_flavour')) {
                $table->string('cake_flavour', 150)->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('bakery_orders', 'weight')) {
                $table->string('weight', 50)->nullable()->after('cake_flavour');
            }

            if (! Schema::hasColumn('bakery_orders', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->nullable()->after('shipping');
            }
        });

        DB::table('bakery_orders')
            ->whereNull('cake_flavour')
            ->whereNotNull('flavour')
            ->update(['cake_flavour' => DB::raw('flavour')]);

        DB::table('bakery_orders')
            ->whereNull('total_amount')
            ->update(['total_amount' => DB::raw('total')]);

        DB::table('bakery_orders')
            ->select('id', 'weight_value', 'weight_unit')
            ->whereNull('weight')
            ->whereNotNull('weight_value')
            ->orderBy('id')
            ->get()
            ->each(function ($order) {
                $weight = trim(((string) $order->weight_value).' '.((string) $order->weight_unit));

                DB::table('bakery_orders')
                    ->where('id', $order->id)
                    ->update(['weight' => $weight ?: null]);
            });
    }

    public function down(): void
    {
        Schema::table('bakery_orders', function (Blueprint $table) {
            if (Schema::hasColumn('bakery_orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }

            if (Schema::hasColumn('bakery_orders', 'weight')) {
                $table->dropColumn('weight');
            }

            if (Schema::hasColumn('bakery_orders', 'cake_flavour')) {
                $table->dropColumn('cake_flavour');
            }

            if (Schema::hasColumn('bakery_orders', 'order_type')) {
                $table->dropColumn('order_type');
            }
        });
    }
};
