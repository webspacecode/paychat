<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bakery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('bakery_order_no', 60)->unique();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('fulfillment_type', 30)->default('pickup');
            $table->timestamp('fulfillment_at')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('status', 50)->default('booked');
            $table->string('payment_status', 50)->default('unpaid');
            $table->string('flavour', 150)->nullable();
            $table->decimal('weight_value', 10, 2)->nullable();
            $table->string('weight_unit', 20)->nullable();
            $table->string('cake_message', 255)->nullable();
            $table->text('design_notes')->nullable();
            $table->string('reference_image_path')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('location_id');
            $table->index('customer_id');
            $table->index('fulfillment_at');
            $table->index('status');
            $table->index('payment_status');
            $table->index(['status', 'fulfillment_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bakery_orders');
    }
};
