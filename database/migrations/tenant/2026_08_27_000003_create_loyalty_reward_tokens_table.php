<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_reward_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('pos_customers')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->index('customer_id');
            $table->index('expires_at');
            $table->index('revoked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_reward_tokens');
    }
};
