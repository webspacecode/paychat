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
        Schema::create('tax_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('gst_number')->nullable();

            // Toggle
            $table->boolean('is_gst_enabled')->default(true);
            $table->boolean('is_inclusive')->default(false);

            // 🔥 NEW: Tax Rates
            $table->decimal('cgst_rate', 5, 2)->default(0); // e.g. 9.00
            $table->decimal('sgst_rate', 5, 2)->default(0); // e.g. 9.00
            $table->decimal('igst_rate', 5, 2)->default(0); // e.g. 18.00

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_config');
    }
};
