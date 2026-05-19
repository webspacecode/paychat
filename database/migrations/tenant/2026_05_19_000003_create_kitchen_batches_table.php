<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('table_session_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->integer('batch_number');
            $table->string('batch_code', 50);
            $table->date('business_date')->nullable();
            $table->string('status', 30)->default('waiting');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('location_id');
            $table->index('order_id');
            $table->index('table_session_id');
            $table->index('table_id');
            $table->index('business_date');
            $table->index('status');
            $table->unique(['batch_code', 'business_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_batches');
    }
};
