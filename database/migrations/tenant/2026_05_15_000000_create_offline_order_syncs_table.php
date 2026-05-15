<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_order_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('local_order_id', 100);
            $table->unsignedBigInteger('backend_order_id')->nullable();
            $table->string('status', 30)->default('processing');
            $table->json('payload');
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'local_order_id']);
            $table->index('backend_order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_order_syncs');
    }
};
