<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('type', 30);
            $table->integer('points');
            $table->decimal('amount', 15, 2)->nullable();
            $table->integer('balance_after');
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('pos_customers')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('order_id')
                ->references('id')
                ->on('pos_orders')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('customer_id');
            $table->index('order_id');
            $table->index('type');
            $table->index('created_at');
            $table->unique(['order_id', 'type'], 'loyalty_transactions_order_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
