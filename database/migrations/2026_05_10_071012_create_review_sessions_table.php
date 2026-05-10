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
        Schema::create('review_sessions', function (Blueprint $table) {

        $table->id();

        /*
        |--------------------------------------------------------------------------
        | Tenant
        |--------------------------------------------------------------------------
        */

        $table->unsignedBigInteger('tenant_id');

        $table->string('tenant_slug');

        $table->string('tenant_api_key');

        /*
        |--------------------------------------------------------------------------
        | Order
        |--------------------------------------------------------------------------
        */

        $table->string('invoice_number');

        $table->string('order_id')
            ->nullable();

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        $table->string('customer_name')
            ->nullable();

        $table->string('customer_phone')
            ->nullable();

        /*
        |--------------------------------------------------------------------------
        | Review Access
        |--------------------------------------------------------------------------
        */

        $table->string('review_token')
            ->unique();

        $table->boolean('is_reviewed')
            ->default(false);

        $table->timestamp('reviewed_at')
            ->nullable();

        /*
        |--------------------------------------------------------------------------
        | Metadata
        |--------------------------------------------------------------------------
        */

        $table->timestamp('expires_at')
            ->nullable();

        $table->timestamps();

        /*
        |--------------------------------------------------------------------------
        | Indexes
        |--------------------------------------------------------------------------
        */

        $table->index('tenant_id');

        $table->index('tenant_slug');

        $table->index('review_token');

        $table->index('invoice_number');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_sessions');
    }
};
