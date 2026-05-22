<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('pending');
            $table->text('failed_reason')->nullable();
            $table->timestamp('setup_started_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
            $table->timestamps();

            $table->unique('tenant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboardings');
    }
};
