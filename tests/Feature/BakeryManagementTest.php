<?php

namespace Tests\Feature;

use App\Models\Tenant\BakeryOrder;
use App\Models\Tenant\BakeryOrderItem;
use App\Models\Tenant\BakeryOrderPayment;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderToken;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Product;
use App\Http\Middleware\EnsureIndustry;
use App\Http\Controllers\Api\Tenant\BakeryOrderController;
use App\Services\Bakery\BakeryOrderService;
use App\Services\Bakery\BakeryPaymentService;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
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
        $this->assertSame('partial', $order->fresh()->payment_status);
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

        $this->assertSame('partial', $order->payment_status);
        $this->assertSame('500.00', (string) $order->paid_amount);
        $this->assertSame('400.00', (string) $order->balance_due);
        $this->assertSame(2, BakeryOrderPayment::count());
        $this->assertSame(0, Payment::count());
    }

    public function test_custom_cake_order_works_without_product_selection(): void
    {
        $order = app(BakeryOrderService::class)->create([
            'order_type' => 'custom_cake',
            'customer_name' => 'Meera',
            'customer_phone' => '99999 11111',
            'fulfillment_type' => 'pickup',
            'cake_flavour' => 'Vanilla',
            'weight' => '2 kg',
            'cake_message' => 'Happy Anniversary',
            'design_notes' => 'White and gold floral finish',
            'total_amount' => 1500,
        ]);

        $this->assertSame('custom_cake', $order->order_type);
        $this->assertSame('Vanilla', $order->cake_flavour);
        $this->assertSame('Vanilla', $order->flavour);
        $this->assertSame('2 kg', $order->weight);
        $this->assertSame('1500.00', (string) $order->total_amount);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame(0, BakeryOrderItem::count());
        $this->assertSame(0, Order::count());
    }

    public function test_ready_cake_booking_snapshots_optional_product_item_without_pos_side_effects(): void
    {
        $product = Product::create([
            'name' => 'Black Forest Ready Cake',
            'sku' => 'CAKE-BF-1KG',
            'type' => 'basic',
            'price' => 850,
            'unit' => 'pcs',
            'track_inventory' => true,
        ]);

        $order = app(BakeryOrderService::class)->create([
            'order_type' => 'ready_cake_booking',
            'customer_name' => 'Rohit',
            'fulfillment_type' => 'delivery',
            'fulfillment_at' => now()->addDays(2)->toISOString(),
            'delivery_address' => '12 Bakery Street',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
            ]],
            'advance_paid' => 500,
        ]);

        $item = $order->fresh('items')->items->first();

        $this->assertSame('ready_cake_booking', $order->order_type);
        $this->assertSame('delivery', $order->fulfillment_type);
        $this->assertSame('12 Bakery Street', $order->delivery_address);
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame('Black Forest Ready Cake', $item->product_name);
        $this->assertSame('CAKE-BF-1KG', $item->sku);
        $this->assertSame('850.00', (string) $item->unit_price);
        $this->assertSame('1700.00', (string) $item->line_total);
        $this->assertSame('1700.00', (string) $order->fresh()->total_amount);
        $this->assertSame('partial', $order->fresh()->payment_status);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, OrderToken::count());
    }

    public function test_bakery_payment_balance_and_status_flow_remain_isolated(): void
    {
        $order = app(BakeryOrderService::class)->create([
            'order_type' => 'event_party',
            'customer_name' => 'Anika',
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'Event Hall, MG Road',
            'total_amount' => 2000,
            'advance_paid' => 750,
        ]);

        app(BakeryPaymentService::class)->recordPayment($order->fresh(), [
            'payment_method' => 'cash',
            'amount' => 1250,
        ]);

        $order = app(BakeryOrderService::class)->updateStatus($order->fresh(), 'confirmed');
        $order = app(BakeryOrderService::class)->updateStatus($order, 'in_production');
        $order = app(BakeryOrderService::class)->updateStatus($order, 'ready');
        $order = app(BakeryOrderService::class)->updateStatus($order, 'delivered');

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('2000.00', (string) $order->paid_amount);
        $this->assertSame('0.00', (string) $order->balance_due);
        $this->assertSame('delivered', $order->status);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, OrderToken::count());
    }

    public function test_reference_image_upload_stores_in_tenant_bakery_path(): void
    {
        Storage::fake('public');

        $request = Request::create('/api/bakery-demo/bakery/orders/reference-image', 'POST', [], [], [
            'image' => UploadedFile::fake()->image('cake-reference.jpg', 800, 600),
        ]);

        $response = app(BakeryOrderController::class)->uploadReferenceImage($request);
        $payload = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertStringStartsWith('tenants/23/bakery/reference-images/', $payload['path']);
        Storage::disk('public')->assertExists($payload['path']);
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

        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('type')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('image_path');
            $table->timestamps();
        });

        Schema::connection('tenant')->create('bakery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('bakery_order_no')->unique();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('order_type')->default('custom_cake');
            $table->string('fulfillment_type')->default('pickup');
            $table->timestamp('fulfillment_at')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('status')->default('booked');
            $table->string('payment_status')->default('unpaid');
            $table->string('cake_flavour')->nullable();
            $table->string('weight')->nullable();
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
            $table->decimal('total_amount', 15, 2)->nullable();
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

        Schema::connection('tenant')->create('bakery_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bakery_order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->json('meta')->nullable();
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
