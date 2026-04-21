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
        Schema::create('order_tokens', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');

            $table->integer('token_number');
            $table->string('token_code');

            $table->date('token_date'); // ✅ important

            $table->string('status')->default('waiting');

            $table->timestamps();

            $table->unique(['token_code', 'token_date']); // ✅ fix
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tokens');
    }
};
