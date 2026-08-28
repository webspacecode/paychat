<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Tenant\CustomerController;
use App\Http\Controllers\Api\Tenant\OrderController;
use App\Models\Tenant\Customer;
use App\Models\Tenant\LoyaltyRewardToken;
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
use Illuminate\Validation\ValidationException;
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

    public function test_new_phone_creates_customer_and_attaches_order(): void
    {
        $order = $this->order(null, 100, 'draft');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'name' => 'New Guest',
            'phone' => '98765 43210',
            'email' => 'guest@example.test',
        ]);

        app(OrderController::class)->attachCustomer('tenant', $request, $order);

        $customer = Customer::where('phone', '9876543210')->first();
        $this->assertNotNull($customer);
        $this->assertSame('New Guest', $customer->name);
        $this->assertSame('guest@example.test', $customer->email);

        $order->refresh();
        $this->assertSame($customer->id, (int) $order->customer_id);
        $this->assertSame('9876543210', $order->customer_phone);
    }

    public function test_new_phone_attach_works_when_address_column_is_missing(): void
    {
        Schema::connection('tenant')->table('pos_customers', function (Blueprint $table) {
            $table->dropColumn('address');
        });

        $order = $this->order(null, 100, 'draft');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'name' => 'Legacy Guest',
            'phone' => '91234 56780',
        ]);

        app(OrderController::class)->attachCustomer('tenant', $request, $order);

        $customer = Customer::where('phone', '9123456780')->first();
        $this->assertNotNull($customer);
        $this->assertSame('Legacy Guest', $customer->name);
    }

    public function test_attach_customer_after_completion_backfills_loyalty_once(): void
    {
        $order = $this->order(null, 300, 'completed');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'name' => 'Late Guest',
            'phone' => '91234 56789',
        ]);

        app(OrderController::class)->attachCustomer('tenant', $request, $order);

        $customer = Customer::where('phone', '9123456789')->first();
        $this->assertNotNull($customer);
        $this->assertSame(3, (int) $customer->fresh()->loyalty_points);
        $this->assertSame(1, (int) $customer->fresh()->total_visits);
        $this->assertDatabaseHas('loyalty_transactions', [
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'earn',
            'points' => 3,
        ]);
    }

    public function test_attach_customer_response_includes_loyalty_context_below_reward_threshold(): void
    {
        $order = $this->order(null, 300, 'completed');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'name' => 'Share Guest',
            'phone' => '90000 00002',
        ]);

        $payload = app(OrderController::class)
            ->attachCustomer('tenant', $request, $order)
            ->toResponse($request)
            ->getData(true)['data'];

        $this->assertSame(3, $payload['loyalty_context']['points_earned_for_order']);
        $this->assertSame(3, $payload['loyalty_context']['loyalty_balance']);
        $this->assertFalse($payload['loyalty_context']['reward_eligible']);
        $this->assertSame(100, $payload['loyalty_context']['reward_threshold']);
        $this->assertNull($payload['loyalty_context']['reward_link']);
    }

    public function test_attach_customer_response_marks_reward_eligible_at_threshold(): void
    {
        $customer = Customer::create([
            'name' => 'Reward Guest',
            'phone' => '9000000003',
            'loyalty_points' => 99,
        ]);
        $order = $this->order($customer, 200, 'completed');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'phone' => '9000000003',
        ]);

        $payload = app(OrderController::class)
            ->attachCustomer('tenant', $request, $order)
            ->toResponse($request)
            ->getData(true)['data'];

        $this->assertSame(2, $payload['loyalty_context']['points_earned_for_order']);
        $this->assertSame(101, $payload['loyalty_context']['loyalty_balance']);
        $this->assertTrue($payload['loyalty_context']['reward_eligible']);
        $this->assertSame(100, $payload['loyalty_context']['reward_threshold']);
        $this->assertIsString($payload['loyalty_context']['reward_link']);
        $this->assertStringContainsString('/loyalty/rewards/', $payload['loyalty_context']['reward_link']);
        $this->assertCount(1, $payload['loyalty_context']['reward_tiers']);
    }

    public function test_ineligible_customer_does_not_get_reward_link(): void
    {
        $customer = Customer::create([
            'name' => 'Almost Guest',
            'phone' => '9000000004',
            'loyalty_points' => 99,
        ]);

        $payload = app(LoyaltyService::class)->sharePayload($customer->fresh());

        $this->assertFalse($payload['reward_eligible']);
        $this->assertNull($payload['reward_link']);
        $this->assertSame([], $payload['reward_tiers']);
    }

    public function test_reward_token_payload_resolves_and_revoked_token_is_invalid(): void
    {
        app()->instance('currentTenant', (new \App\Models\Tenant())->forceFill([
            'slug' => 'tenant',
        ]));

        $customer = Customer::create([
            'name' => 'Reward Link Guest',
            'phone' => '9876543210',
            'loyalty_points' => 150,
        ]);

        $loyalty = app(LoyaltyService::class);
        $link = $loyalty->rewardLinkForCustomer($customer->fresh());
        $token = basename(parse_url($link, PHP_URL_PATH));

        $payload = $loyalty->rewardPayloadForToken($token);
        $this->assertSame(150, $payload['loyalty_balance']);
        $this->assertSame('******3210', $payload['customer']['masked_phone']);
        $this->assertCount(2, $payload['reward_tiers']);
        $this->assertSame('paychat-loyalty:'.$token, $payload['qr_payload']);

        LoyaltyRewardToken::query()->update(['revoked_at' => now()]);

        $this->assertNull($loyalty->rewardPayloadForToken($token));
    }

    public function test_redeem_100_from_150_leaves_50_and_records_transaction(): void
    {
        app()->instance('currentTenant', (new \App\Models\Tenant())->forceFill([
            'slug' => 'tenant',
        ]));

        $customer = Customer::create([
            'name' => 'Redeem Guest',
            'phone' => '9000000010',
            'loyalty_points' => 150,
        ]);
        $loyalty = app(LoyaltyService::class);
        $token = basename(parse_url($loyalty->rewardLinkForCustomer($customer->fresh()), PHP_URL_PATH));

        $payload = $loyalty->redeem($customer->fresh(), [
            'reward_tier_id' => 'reward_100',
            'qr_token' => 'paychat-loyalty:'.$token,
            'idempotency_key' => 'redeem-1',
        ]);

        $this->assertSame(50, $payload['loyalty_balance']);
        $this->assertSame('redeem', $payload['transaction']['type']);
        $this->assertSame(-100, $payload['transaction']['points']);
        $this->assertSame('fixed_discount', $payload['applied_reward']['type']);
        $this->assertSame(50.0, $payload['applied_reward']['discount_amount']);
        $this->assertSame(50, (int) $customer->fresh()->loyalty_points);
        $this->assertDatabaseHas('loyalty_transactions', [
            'customer_id' => $customer->id,
            'type' => 'redeem',
            'points' => -100,
            'balance_after' => 50,
        ]);
        $this->assertNotNull(LoyaltyRewardToken::first()->revoked_at);
    }

    public function test_cannot_redeem_more_than_customer_balance(): void
    {
        app()->instance('currentTenant', (new \App\Models\Tenant())->forceFill([
            'slug' => 'tenant',
        ]));

        $customer = Customer::create([
            'name' => 'Small Balance',
            'phone' => '9000000011',
            'loyalty_points' => 100,
        ]);
        $loyalty = app(LoyaltyService::class);
        $token = basename(parse_url($loyalty->rewardLinkForCustomer($customer->fresh()), PHP_URL_PATH));

        $this->expectException(ValidationException::class);

        $loyalty->redeem($customer->fresh(), [
            'reward_tier_id' => 'reward_150',
            'qr_token' => $token,
            'idempotency_key' => 'redeem-too-much',
        ]);
    }

    public function test_duplicate_redeem_scan_does_not_double_deduct(): void
    {
        app()->instance('currentTenant', (new \App\Models\Tenant())->forceFill([
            'slug' => 'tenant',
        ]));

        $customer = Customer::create([
            'name' => 'Duplicate Redeem',
            'phone' => '9000000012',
            'loyalty_points' => 150,
        ]);
        $loyalty = app(LoyaltyService::class);
        $token = basename(parse_url($loyalty->rewardLinkForCustomer($customer->fresh()), PHP_URL_PATH));
        $payload = [
            'reward_tier_id' => 'reward_100',
            'qr_token' => $token,
            'idempotency_key' => 'same-scan',
        ];

        $first = $loyalty->redeem($customer->fresh(), $payload);
        $second = $loyalty->redeem($customer->fresh(), $payload);

        $this->assertSame($first['transaction']['id'], $second['transaction']['id']);
        $this->assertSame(50, (int) $customer->fresh()->loyalty_points);
        $this->assertDatabaseCount('loyalty_transactions', 1);
    }

    public function test_void_redemption_restores_points_once(): void
    {
        app()->instance('currentTenant', (new \App\Models\Tenant())->forceFill([
            'slug' => 'tenant',
        ]));

        $customer = Customer::create([
            'name' => 'Undo Redeem',
            'phone' => '9000000014',
            'loyalty_points' => 150,
        ]);
        $loyalty = app(LoyaltyService::class);
        $token = basename(parse_url($loyalty->rewardLinkForCustomer($customer->fresh()), PHP_URL_PATH));
        $redeemed = $loyalty->redeem($customer->fresh(), [
            'reward_tier_id' => 'reward_100',
            'qr_token' => $token,
            'idempotency_key' => 'redeem-undo',
        ]);

        $redemption = LoyaltyTransaction::findOrFail($redeemed['transaction']['id']);
        $firstVoid = $loyalty->voidRedemption($customer->fresh(), $redemption);
        $secondVoid = $loyalty->voidRedemption($customer->fresh(), $redemption->fresh());

        $this->assertSame(150, $firstVoid['loyalty_balance']);
        $this->assertSame($firstVoid['transaction']['id'], $secondVoid['transaction']['id']);
        $this->assertSame('redeem_void', $firstVoid['transaction']['type']);
        $this->assertSame(150, (int) $customer->fresh()->loyalty_points);
        $this->assertDatabaseCount('loyalty_transactions', 2);
        $this->assertNull(LoyaltyRewardToken::first()->fresh()->revoked_at);
    }

    public function test_customer_history_includes_redeem_transaction(): void
    {
        $customer = Customer::create([
            'name' => 'History Redeem',
            'phone' => '9000000013',
            'loyalty_points' => 50,
        ]);

        LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'type' => 'redeem',
            'points' => -100,
            'balance_after' => 50,
            'description' => 'Points redeemed: 100 point reward',
        ]);

        $payload = app(CustomerController::class)
            ->loyaltyTransactions('tenant', Request::create('/customers/'.$customer->id.'/loyalty-transactions'), $customer)
            ->getData(true);

        $this->assertSame('redeem', $payload['data'][0]['type']);
        $this->assertSame(-100, $payload['data'][0]['points']);
    }

    public function test_repeated_customer_attach_does_not_double_award_loyalty(): void
    {
        $order = $this->order(null, 300, 'completed');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'name' => 'Repeat Guest',
            'phone' => '91234 56789',
        ]);

        app(OrderController::class)->attachCustomer('tenant', $request, $order);
        app(OrderController::class)->attachCustomer('tenant', $request, $order->fresh());

        $customer = Customer::where('phone', '9123456789')->first();
        $this->assertSame(3, (int) $customer->fresh()->loyalty_points);
        $this->assertSame(1, (int) $customer->fresh()->total_visits);
        $this->assertDatabaseCount('loyalty_transactions', 1);
    }

    public function test_completed_order_attach_still_succeeds_when_loyalty_table_is_missing(): void
    {
        Schema::connection('tenant')->dropIfExists('loyalty_transactions');

        $order = $this->order(null, 300, 'completed');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'name' => 'Legacy Loyalty Guest',
            'phone' => '92345 67890',
        ]);

        app(OrderController::class)->attachCustomer('tenant', $request, $order);

        $customer = Customer::where('phone', '9234567890')->first();
        $this->assertNotNull($customer);
        $order->refresh();
        $this->assertSame($customer->id, (int) $order->customer_id);
    }

    public function test_invalid_customer_id_returns_validation_error(): void
    {
        $this->expectException(ValidationException::class);

        $order = $this->order(null, 100, 'draft');
        $request = Request::create('/orders/'.$order->id.'/customer', 'PATCH', [
            'customer_id' => 999,
        ]);

        app(OrderController::class)->attachCustomer('tenant', $request, $order);
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
        $this->assertEquals(450.0, $payload['total_spend']);
        $this->assertEquals(225.0, $payload['average_order_value']);
        $this->assertSame('Latte', $payload['favourite_products'][0]['product_name']);
        $this->assertCount(1, $payload['recent_orders']);
        $this->assertCount(1, $payload['recent_loyalty_transactions']);
    }

    public function test_customer_index_includes_visit_spend_and_last_visit_fields(): void
    {
        Customer::create([
            'name' => 'Asha',
            'phone' => '9998887777',
            'loyalty_points' => 4,
            'total_visits' => 2,
            'total_spend' => 450,
            'last_visit_at' => Carbon::parse('2026-06-04 10:00:00'),
        ]);

        $payload = app(CustomerController::class)
            ->index(Request::create('/customers', 'GET'))
            ->getData(true);

        $customer = $payload['data'][0];

        $this->assertSame(4, $customer['loyalty_points']);
        $this->assertSame(2, $customer['total_visits']);
        $this->assertEquals(450.0, $customer['total_spend']);
        $this->assertNotEmpty($customer['last_visit_at']);
    }

    public function test_summary_works_when_loyalty_table_is_missing(): void
    {
        Schema::connection('tenant')->dropIfExists('loyalty_transactions');

        $customer = Customer::create([
            'name' => 'Legacy Summary Guest',
            'phone' => '9000000001',
            'loyalty_points' => 0,
            'total_visits' => 0,
            'total_spend' => 0,
        ]);

        $payload = app(CustomerController::class)
            ->summary('tenant', $customer->fresh())
            ->getData(true);

        $this->assertSame(0, $payload['loyalty_points']);
        $this->assertSame([], $payload['recent_loyalty_transactions']);
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

        Schema::connection('tenant')->create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
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

        Schema::connection('tenant')->create('pos_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('upi_profile_id')->nullable();
            $table->text('upi_qr_url')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('transaction_id')->nullable();
            $table->string('status')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('kitchen_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('table_session_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->integer('batch_number')->nullable();
            $table->string('batch_code')->nullable();
            $table->date('business_date')->nullable();
            $table->string('status')->nullable();
            $table->string('dispatch_channel')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('order_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('token_code')->nullable();
            $table->string('status')->nullable();
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
            $table->string('redemption_key', 64)->nullable()->unique();
            $table->timestamps();
            $table->unique(['order_id', 'type']);
        });

        Schema::connection('tenant')->create('loyalty_reward_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
}
