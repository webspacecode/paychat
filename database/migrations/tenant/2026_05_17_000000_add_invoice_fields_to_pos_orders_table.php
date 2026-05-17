<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'invoice_no')) {
                $table->string('invoice_no', 50)->nullable()->unique();
            }

            if (!Schema::hasColumn('pos_orders', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'invoice_id')) {
                $table->dropUnique(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
        });
    }
};
