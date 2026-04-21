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
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            // Reference to payment
            $table->unsignedBigInteger('payment_id');

            // Transaction details
            $table->string('transaction_ref', 100)->nullable(); // External payment reference ID
            $table->enum('status', ['initiated','success','failed','cancelled'])->default('initiated');
            $table->decimal('amount', 12, 2)->nullable(); // Transaction amount (could be partial)
            $table->string('payment_gateway', 50)->nullable(); // Name of payment gateway (e.g., Razorpay, Stripe)
            $table->string('gateway_response', 500)->nullable(); // Raw response from gateway for debugging/logs
            $table->string('upi_qr_url', 255)->nullable(); // If UPI payment
            $table->string('remarks', 255)->nullable(); // Optional notes or reason for failure
            $table->string('initiated_by', 100)->nullable(); // User or system who initiated

            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            // $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade')->onUpdate('cascade');

            // Indexes
            $table->index('payment_id', 'idx_transactions_payment');
            $table->index('status', 'idx_transactions_status');
            $table->index('transaction_ref', 'idx_transactions_ref');
            $table->index(['payment_id', 'status'], 'idx_transactions_payment_status');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};
