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
         Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('type')->nullable()->index();
            //  $table->enum('type', ['basic', 'raw', 'semi_finished', 'finished', 'recipe', 'other']);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('unit')->nullable(); // e.g. kg, litre, pcs
            $table->boolean('track_inventory')->default(true);
            $table->timestamps();

            $table->index(['name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
