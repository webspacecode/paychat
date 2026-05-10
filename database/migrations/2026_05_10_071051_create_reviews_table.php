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
        Schema::create('reviews', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('review_session_id');

            $table->unsignedBigInteger('tenant_id');

            $table->string('tenant_slug');

            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            $table->tinyInteger('rating');

            $table->text('review_text')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Moderation
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_verified_purchase')
                ->default(true);

            $table->boolean('is_approved')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            $table->ipAddress('ip_address')
                ->nullable();

            $table->string('user_agent')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('tenant_id');

            $table->index('tenant_slug');

            $table->index('rating');

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
