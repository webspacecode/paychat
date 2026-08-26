<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_method_corrections')) {
            return;
        }

        Schema::create('payment_method_corrections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_id');
            $table->string('old_payment_method', 50);
            $table->string('new_payment_method', 50);
            $table->unsignedBigInteger('old_upi_profile_id')->nullable();
            $table->unsignedBigInteger('new_upi_profile_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->unsignedBigInteger('corrected_by')->nullable();
            $table->timestamp('corrected_at')->nullable();
            $table->char('idempotency_key_hash', 64)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('pos_orders')->cascadeOnDelete();
            $table->foreign('payment_id')->references('id')->on('pos_payments')->cascadeOnDelete();

            $table->index('order_id');
            $table->index('payment_id');
            $table->index('corrected_by');
            $table->index('corrected_at');
            $table->index('old_payment_method');
            $table->index('new_payment_method');
            $table->index('idempotency_key_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_corrections');
    }
};
