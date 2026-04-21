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
        Schema::create('pos_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');

            $table->enum('payment_method', ['upi','cash','phonepe']);

            $table->string('mode')->nullable();       // personal | business
            $table->string('provider')->nullable();   // phonepe, razorpay

            $table->decimal('amount', 12, 2);

            $table->string('transaction_id', 100)->nullable();
            $table->string('provider_ref', 150)->nullable();

            $table->string('upi_qr_url', 255)->nullable();

            // ✅ FINAL STATUS (only once)
            $table->enum('status', [
                'pending',
                'processing',
                'success',
                'failed',
                'expired'
            ])->default('pending');

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FK
            $table->foreign('order_id')
                ->references('id')
                ->on('pos_orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Indexes
            $table->index('order_id');
            $table->index('status');
            $table->index('payment_method');
            $table->index(['order_id', 'status']);
            $table->index(['order_id', 'payment_method']);
            $table->index('provider_ref'); // ✅ now safe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_payments');
    }
};
