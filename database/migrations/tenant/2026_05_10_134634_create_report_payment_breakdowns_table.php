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
        Schema::create('report_payment_breakdowns', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();

            $table->date('date');

            $table->string('payment_method'); // upi, cash, card
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);

            $table->timestamps();

            $table->index(['tenant_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_payment_breakdowns');
    }
};
