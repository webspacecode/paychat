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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();

            $table->string('provider', 100)->index(); // e.g., Razorpay, Stripe
            $table->json('payload'); // Store webhook data as JSON for easy querying
            $table->enum('status', ['received','processed','failed'])->default('received')->index();
            $table->string('event_type', 100)->nullable()->index(); // Type of webhook event, if provider supports it
            $table->string('reference_id', 100)->nullable()->index(); // Optional reference to related order/payment/transaction
            $table->timestamps();
            $table->softDeletes(); // Keep history even if logically deleted

            // Composite index for common queries
            $table->index(['provider','status'], 'idx_webhook_provider_status');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_webhook_logs');
    }
};
