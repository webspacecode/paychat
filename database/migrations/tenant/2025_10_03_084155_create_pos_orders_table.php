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
        Schema::create('pos_orders', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('order_no', 50)->unique();        // Internal running order number
            $table->string('invoice_no', 50)->nullable()->unique();
            $table->string('reference_no', 100)->nullable(); // External reference

            /*
            |--------------------------------------------------------------------------
            | Business Context
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('location_id');       // Branch / Store
            $table->string('order_type', 50)->default('pos'); 
            // pos, dine_in, takeaway, delivery, online, wholesale, etc

            $table->unsignedBigInteger('table_id')->nullable();     // Restaurant
            $table->unsignedBigInteger('warehouse_id')->nullable(); // Retail warehouse logic

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('customer_id')->nullable();

            // Walk-in fallback (if no customer record)
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 50)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Staff Tracking
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('created_by')->nullable();   // cashier
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status Management
            |--------------------------------------------------------------------------
            */

            $table->string('status', 50)->default('draft');
            // draft, pending_payment, paid, cancelled, refunded, void

            $table->string('payment_status', 50)->default('unpaid');
            // unpaid, partially_paid, paid, refunded

            /*
            |--------------------------------------------------------------------------
            | Financials
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->decimal('rounding', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->decimal('change_returned', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Extra Metadata
            |--------------------------------------------------------------------------
            */

            $table->string('currency', 10)->default('INR');
            $table->text('notes')->nullable();
            $table->json('meta')->nullable(); // flexible extra data

            /*
            |--------------------------------------------------------------------------
            | Time Tracking
            |--------------------------------------------------------------------------
            */

            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            
            /*
            | Tokens for Token Managment
            */
            $table->unsignedBigInteger('token_id')->nullable();
            
            /*
            |--------------------------------------------------------------------------
            | Indexing
            |--------------------------------------------------------------------------
            */

            $table->index('location_id');
            $table->index('token_id');
            $table->index('customer_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index(['location_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};
