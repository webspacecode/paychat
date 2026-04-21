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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();

            $table->integer('quantity'); // negative for sale
            $table->enum('type', ['pos_sale','refund','adjustment']);

            $table->unsignedBigInteger('reference_id')->nullable(); // order id
            $table->string('reference_type')->nullable(); // pos_order

            $table->timestamps();

            $table->index(['product_id','location_id']);
            $table->index(['reference_type','reference_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
