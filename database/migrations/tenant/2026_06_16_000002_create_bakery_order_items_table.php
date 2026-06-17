<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bakery_order_items')) {
            return;
        }

        Schema::create('bakery_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bakery_order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name', 255);
            $table->string('sku', 100)->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bakery_order_id')
                ->references('id')
                ->on('bakery_orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('bakery_order_id');
            $table->index('product_id');
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bakery_order_items');
    }
};
