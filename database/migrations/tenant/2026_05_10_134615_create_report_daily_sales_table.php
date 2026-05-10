<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_daily_sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();

            $table->date('date');

            $table->integer('total_orders')->default(0);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('net_sales', 12, 2)->default(0);
            $table->decimal('avg_order_value', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['tenant_id', 'location_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_daily_sales');
    }
};
