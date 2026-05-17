<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unique(['tenant_id', 'order_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'order_id')) {
                $table->dropUnique(['tenant_id', 'order_id']);
                $table->dropColumn('order_id');
            }
        });
    }
};
