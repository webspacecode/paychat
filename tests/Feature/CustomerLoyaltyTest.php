<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Tenant\CustomerController;
use App\Http\Controllers\Api\Tenant\OrderController;
use App\Models\Tenant\Customer;
use App\Models\Tenant\LoyaltyTransaction;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\Product;
use App\Models\Tenant\Setting;
use App\Services\Inventory\StockAvailabilityService;
use App\Services\LoyaltyService;
use App\Services\Orders\OrderService;
use App\Services\Payments\TaxService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerLoyaltyTest extends TestCase
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

        $this->createTenantSchema();
    }

    public function test_no_customer_order_earns_nothing(): void
    {
        $order = $this->order(null, 250);

        $this->completeOrder($order);

        $this->assertDatabaseCount('loyalty_transactions', 0);
    }

    public function test_completed_paid_customer_order_earns_points_once(): void
    {
        $customer = Customer::create(['name' => 'Asha', 'phone' => '9998887777']);
        $order = $this->order($customer, 250);

        $this->completeOrder($order);

        $customer->refresh();
        $this->assertSame(2, (int) $customer->loyalty_points);
        $this->assertSame(1, (int) $customer->total_visits);
        $this->assertSame(250.0, (float) $customer->total_spend);
        $this->assertDatabaseHas('loyalty_transactions', [
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'earn',
            'points' => 2,
        ]);
    }

    public function test_duplicate_completion_callback_does_not_double_award(): void
    {
        $customer = Customer::create(['name' => 'Asha', 'phone' => '9998887777']);
        $order = $this->order($customer, 250, 'completed');

        $loyalty = app(LoyaltyService::class);
        $loyalty->awardForCompletedOrder($order);
        $loyalty->awardForCompletedOrder($order->fresh());

        $customer->refresh();
        $this->assertSame(2, (int) $customer->loyalty_points);
        $this->assertSame(1, (int) $customer->total_visits);
        $this->assertDatabaseCount('loyalty_transactions', 1);
    }

    public function test_offline_synced_customer_order_earns_once(): void
    {
        $customer = Customer::create(['name' => 'Offline Guest', 'phone' => '9000000000']);
        $order = $this->order($customer, 320, 'completed', [
            'offline' => true,
            'local_order_id' => 'local-1',
        ]);

        app(LoyaltyService::class)->awardForCompletedOrder($order);
        app(LoyaltyService::class)->awardForCompletedOrder($order->fresh());

        $customer->refresh();
        $this->assertSame(3, (int) $customer->loyalty_points);
        $this->assertSame(1, (int) $customer->total_visits);
        $this->assertDatabaseCount('loyalty_transactions', 1);
    }

    public function test_cancelled_refunded_and_draft_orders_earn_nothing(): void
    {
        $customer = Customer::create(['name' => 'Asha', 'phone' => '9998887777']);

        foreach (['cancelled', 'refunded', 'draft'] as $status) {
            app(LoyaltyService::class)->awardForCompletedOrder(
                $this->order($customer, 250, $status)
            );
        }

        $customer->refresh();
        $this->assertSame(0, (int) $customer->loyalty_points);
        $this->assertSame(0, (int) $customer->total_visits);
        $this->assertDatabaseCount('loyalty_transactions', 0);
    }

    public function test_loyalty_disabled_earns_nothing(): void
    {
        Setting::set('loyalty', [
            'enabled' => false,
            'points_per_100' => 1,
            'minimum_redemption' => 50,
            'redeem_value_per_point' => 1,
            'earn_on_discounted_total' => true,
        ], 'json');

        $customer = Customer::create(['name' => 'Asha', 'phone' => '9998887777']);

        app(LoyaltyService::class)->awardForCompletedOrder(
            $this->order($customer, 250, 'completed')
        );

        $customer->refresh();
        $this->assertSame(0, (int) $customer->loyalty_points);
        $this->assertDatabaseCount('loyalty_transactions', 0);
    }

    public function test_existing_phone_attaches_customer_correctly(): void
    {
        $customer = Customer::create(['name' => 'Asha', 'phone' => '9998887777']);
        $order = $this->order(null, 100, 'draft');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'phone' => '999-888-7777',
        ]);

        app(OrderController::class)->attachCustomer('tenant', $request, $order);

        $order->refresh();
        $this->assertSame($customer->id, (int) $order->customer_id);
        $this->assertSame('9998887777', $order->customer_phone);
    }

    public function test_summary_returns_correct_counters(): void
    {
        $customer = Customer::create([
            'name' => 'Asha',
            'phone' => '9998887777',
            'loyalty_points' => 4,
            'total_visits' => 2,
            'total_spend' => 450,
            'last_visit_at' => Carbon::parse('2026-06-04 10:00:00'),
        ]);
        $product = Product::create(['name' => 'Latte', 'sku' => 'LATTE', 'price' => 150, 'track_inventory' => false]);
        $order = $this->order($customer, 300, 'completed');
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 150,
            'total' => 300,
        ]);
        LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'earn',
            'points' => 3,
            'amount' => 300,
            'balance_after' => 4,
        ]);

        $payload = app(CustomerController::class)
            ->summary('tenant', $customer->fresh())
            ->getData(true);

        $this->assertSame(4, $payload['loyalty_points']);
        $this->assertSame(2, $payload['total_visits']);
        $this->assertSame(450.0, $payload['total_spend']);
        $this->assertSame(225.0, $payload['average_order_value']);
        $this->assertSame('Latte', $payload['favourite_products'][0]['product_name']);
        $this->assertCount(1, $payload['recent_orders']);
        $this->assertCount(1, $payload['recent_loyalty_transactions']);
    }

    public function test_transactions_are_latest_first(): void
    {
        $customer = Customer::create(['name' => 'Asha', 'phone' => '9998887777']);

        LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'type' => 'earn',
            'points' => 1,
            'balance_after' => 1,
            'created_at' => Carbon::parse('2026-06-04 09:00:00'),
            'updated_at' => Carbon::parse('2026-06-04 09:00:00'),
        ]);
        LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'type' => 'adjustment',
            'points' => 2,
            'balance_after' => 3,
            'created_at' => Carbon::parse('2026-06-04 11:00:00'),
            'updated_at' => Carbon::parse('2026-06-04 11:00:00'),
        ]);

        $payload = app(CustomerController::class)
            ->loyaltyTransactions('tenant', Request::create('/customers/'.$customer->id.'/loyalty-transactions'), $customer)
            ->getData(true);

        $this->assertSame('adjustment', $payload['data'][0]['type']);
        $this->assertSame('earn', $payload['data'][1]['type']);
    }

    private function completeOrder(Order $order): void
    {
        $service = new OrderService(new TaxService(), app(StockAvailabilityService::class));
        $service->completeOrder($order);
    }

    private function order(?Customer $customer, float $total, string $status = 'pending_payment', ?array $meta = null): Order
    {
        return Order::create([
            'order_no' => 'ORD-'.str()->uuid(),
            'location_id' => 1,
            'customer_id' => $customer?->id,
            'customer_name' => $customer?->name,
            'customer_phone' => $customer?->phone,
            'status' => $status,
            'payment_status' => 'paid',
            'subtotal' => $total,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'paid_amount' => $total,
            'balance_due' => 0,
            'completed_at' => $status === 'completed' ? now() : null,
            'meta' => $meta,
        ]);
    }

    private function createTenantSchema(): void
    {
        Schema::connection('tenant')->create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->nullable();
            $table->string('phone', 50)->nullable()->index();
            $table->string('email', 150)->nullable()->index();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('customer_type', 50)->default('walk_in');
            $table->integer('loyalty_points')->default(0);
            $table->integer('total_visits')->default(0);
            $table->decimal('total_spend', 15, 2)->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique();
            $table->string('invoice_no', 50)->nullable()->unique();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('status', 50)->default('draft');
            $table->string('payment_status', 50)->default('unpaid');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->date('business_date')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('type')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('track_inventory')->default(false);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('type', 30);
            $table->integer('points');
            $table->decimal('amount', 15, 2)->nullable();
            $table->integer('balance_after');
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'type']);
        });
    }
}
