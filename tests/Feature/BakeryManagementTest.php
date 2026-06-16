<?php

namespace Tests\Feature;

use App\Models\Tenant\BakeryOrder;
use App\Models\Tenant\BakeryOrderPayment;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderToken;
use App\Models\Tenant\Payment;
use App\Http\Middleware\EnsureIndustry;
use App\Services\Bakery\BakeryOrderService;
use App\Services\Bakery\BakeryPaymentService;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BakeryManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        DB::connection('tenant')->reconnect();

        app()->instance('currentTenant', (object) [
            'id' => 23,
            'slug' => 'bakery-demo',
            'industry' => 'bakery',
        ]);

        $this->createTenantSchema();
    }

    public function test_bakery_order_with_advance_uses_bakery_tables_only(): void
    {
        $order = app(BakeryOrderService::class)->create([
            'customer_name' => 'Asha',
            'customer_phone' => '98765 43210',
            'fulfillment_type' => 'pickup',
            'fulfillment_at' => now()->addDay()->toISOString(),
            'flavour' => 'Chocolate',
            'weight_value' => 1.5,
            'weight_unit' => 'kg',
            'cake_message' => 'Happy Birthday',
            'subtotal' => 1200,
            'discount' => 100,
            'tax' => 0,
            'shipping' => 0,
            'advance_paid' => 500,
            'advance_payment_method' => 'cash',
        ], 7);

        $this->assertSame(1, BakeryOrder::count());
        $this->assertSame(1, BakeryOrderPayment::count());
        $this->assertSame('advance_paid', $order->fresh()->payment_status);
        $this->assertSame('500.00', (string) $order->fresh()->paid_amount);
        $this->assertSame('600.00', (string) $order->fresh()->balance_due);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, OrderToken::count());
    }

    public function test_additional_bakery_payment_updates_balance_without_pos_payment_service(): void
    {
        $order = app(BakeryOrderService::class)->create([
            'customer_name' => 'Riya',
            'subtotal' => 900,
            'advance_paid' => 200,
            'advance_payment_method' => 'cash',
        ]);

        app(BakeryPaymentService::class)->recordPayment($order->fresh(), [
            'payment_method' => 'upi',
            'amount' => 300,
        ]);

        $order = $order->fresh();

        $this->assertSame('partially_paid', $order->payment_status);
        $this->assertSame('500.00', (string) $order->paid_amount);
        $this->assertSame('400.00', (string) $order->balance_due);
        $this->assertSame(2, BakeryOrderPayment::count());
        $this->assertSame(0, Payment::count());
    }

    public function test_industry_gate_rejects_non_bakery_tenant(): void
    {
        app()->instance('currentTenant', (object) [
            'id' => 23,
            'slug' => 'cafe-demo',
            'industry' => 'cafe',
        ]);

        $this->expectException(HttpException::class);

        app(EnsureIndustry::class)->handle(
            Request::create('/api/cafe-demo/bakery/orders'),
            fn () => response()->json(['ok' => true]),
            'bakery'
        );
    }

    private function createTenantSchema(): void
    {
        Schema::connection('tenant')->create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('order_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('token_code')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('bakery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('bakery_order_no')->unique();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('fulfillment_type')->default('pickup');
            $table->timestamp('fulfillment_at')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('status')->default('booked');
            $table->string('payment_status')->default('unpaid');
            $table->string('flavour')->nullable();
            $table->decimal('weight_value', 10, 2)->nullable();
            $table->string('weight_unit')->nullable();
            $table->string('cake_message')->nullable();
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
        });

        Schema::connection('tenant')->create('bakery_order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bakery_order_id');
            $table->string('payment_method');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('success');
            $table->string('transaction_id')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_ref')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
