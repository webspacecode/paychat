<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_product_image_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('provider');
            $table->string('query');
            $table->string('provider_image_id')->nullable();
            $table->text('preview_url')->nullable();
            $table->text('full_url')->nullable();
            $table->string('photographer_name')->nullable();
            $table->text('photographer_url')->nullable();
            $table->string('license')->nullable();
            $table->string('status')->default('suggested');
            $table->text('error_message')->nullable();
            $table->timestamp('searched_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'provider', 'status'], 'epis_product_provider_status_idx');
            $table->index(['product_id', 'provider', 'query'], 'epis_product_provider_query_idx');
            $table->index('searched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_product_image_suggestions');
    }
};
