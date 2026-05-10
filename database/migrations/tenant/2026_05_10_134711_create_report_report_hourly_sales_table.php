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
        Schema::create('report_hourly_sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();

            $table->date('date');
            $table->tinyInteger('hour');

            $table->integer('orders_count')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_report_hourly_sales');
    }
};
