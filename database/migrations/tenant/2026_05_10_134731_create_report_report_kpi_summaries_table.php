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
        Schema::create('report_kpi_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('date');

            $table->decimal('sales', 12, 2)->default(0);
            $table->integer('orders')->default(0);
            $table->decimal('avg_order', 12, 2)->default(0);
            $table->decimal('growth_percent', 5, 2)->default(0);
            $table->tinyInteger('peak_hour')->nullable();
            $table->unsignedBigInteger('top_product_id')->nullable();

            $table->decimal('upi_percent', 5, 2)->default(0);
            $table->decimal('cash_percent', 5, 2)->default(0);
            $table->decimal('card_percent', 5, 2)->default(0);

            $table->timestamps();

            $table->unique(['tenant_id', 'location_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_kpi_summaries');
    }
};
