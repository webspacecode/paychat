<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idempotency_requests', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 100);
            $table->char('idempotency_key_hash', 64);
            $table->char('request_hash', 64);
            $table->string('status', 20)->default('processing');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->string('resource_type', 100)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            // DATETIME avoids the implicit-default restrictions of older MySQL
            // versions/sql modes while retaining the required application semantics.
            $table->dateTime('locked_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('expires_at')->index();
            $table->timestamps();
            $table->unique(['scope', 'idempotency_key_hash'], 'idempotency_scope_key_unique');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_requests');
    }
};
