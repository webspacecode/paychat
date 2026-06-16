<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bakery_order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bakery_order_id');
            $table->string('payment_method', 50);
            $table->decimal('amount', 15, 2);
            $table->string('status', 50)->default('success');
            $table->string('transaction_id', 100)->nullable();
            $table->string('provider', 100)->nullable();
            $table->string('provider_ref', 150)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bakery_order_id')
                ->references('id')
                ->on('bakery_orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->index('bakery_order_id');
            $table->index('payment_method');
            $table->index('status');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bakery_order_payments');
    }
};
