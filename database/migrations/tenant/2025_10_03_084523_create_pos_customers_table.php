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
        Schema::create('pos_customers', function (Blueprint $table) {
            $table->id();

            // Basic identity
            $table->string('name',150)->nullable();
            $table->string('phone',50)->nullable()->index();
            $table->string('email',150)->nullable()->index();

            // Business context
            $table->unsignedBigInteger('location_id')->nullable(); 
            $table->string('customer_type',50)->default('walk_in'); 
            // walk_in, regular, wholesale, member

            // Loyalty / marketing
            $table->integer('loyalty_points')->default(0);

            // Extra data
            $table->json('meta')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_customers');
    }
};
