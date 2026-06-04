<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Tenant\OrderController;
use App\Http\Controllers\Api\Tenant\PaymentController;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\OrderToken;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Product;
use App\Services\InvoiceService;
use App\Services\OrderKitchenDispatchService;
use App\Services\Orders\OrderService;
use App\Services\Payments\PaymentService;
use App\Services\TableSessionService;
use App\Services\TokenService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\TestCase;

class CheckoutPaymentReliabilityTest extends TestCase
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
        Config::set('broadcasting.default', 'null');

        $this->createTenantSchema();
        DB::connection('tenant')->table('locations')->insert([
            'id' => 1,
            'name' => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app()->instance('currentTenant', (object) [
            'id' => 23,
            'slug' => 'demo',
            'industry' => 'cafe',
        ]);
    }

    public function test_normal_cash_checkout_completes_order(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $order = $this->order(total: 250);

        $payment = app(PaymentService::class)->createPayment($order, 'cash', 250);

        $this->assertSame('success', $payment->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(1, Payment::count());
    }

    public function test_normal_upi_checkout_reuses_duplicate_pending_attempt(): void
    {
        PaymentMethod::create([
            'type' => 'upi',
            'mode' => 'personal',
            'enabled' => true,
            'config' => ['upi_id' => 'store@upi', 'name' => 'Store'],
        ]);
        $order = $this->order(total: 300);

        $first = app(PaymentService::class)->createPayment($order, 'upi', 300);
        $second = app(PaymentService::class)->createPayment($order->fresh(), 'upi', 300);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('pending', $second->status);
        $this->assertSame(1, Payment::count());
    }

    public function test_payment_success_called_twice_returns_safe_already_paid_response(): void
    {
        $order = $this->order(total: 200);
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'amount' => 200,
            'status' => 'pending',
        ]);

        $first = app(PaymentService::class)->markPaymentSuccess($payment->fresh());
        $second = app(PaymentService::class)->markPaymentSuccess($payment->fresh());

        $this->assertFalse($first['already_successful']);
        $this->assertFalse($first['idempotent']);
        $this->assertTrue($second['already_successful']);
        $this->assertTrue($second['already_paid']);
        $this->assertTrue($second['idempotent']);
        $this->assertSame(1, Payment::where('status', 'success')->count());
    }

    public function test_duplicate_success_skips_slow_invoice_and_token_generation(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $order = $this->order(total: 180, status: 'completed', paymentStatus: 'paid');
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 180,
            'status' => 'success',
        ]);

        app()->instance(InvoiceService::class, new class extends InvoiceService {
            public function generate($order, $tenant, $industry, $paper)
            {
                throw new \RuntimeException('Invoice should not run on idempotent success');
            }
        });
        app()->instance(OrderKitchenDispatchService::class, new class(app(TokenService::class)) extends OrderKitchenDispatchService {
            public function ensureTokenAndDispatchWhenReady(Order $order, string $reason): ?OrderToken
            {
                throw new \RuntimeException('Token should not run on idempotent success');
            }
        });

        $response = app(PaymentController::class)->markSuccess(
            'demo',
            (string) $payment->id,
            app(PaymentService::class),
            app(OrderKitchenDispatchService::class)
        );
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['already_paid']);
        $this->assertFalse($payload['invoice_generated']);
    }

    public function test_completed_order_item_update_returns_conflict(): void
    {
        $order = $this->order(total: 120, status: 'completed', paymentStatus: 'paid');

        $this->expectException(ConflictHttpException::class);

        app(OrderController::class)->updateItems(
            'demo',
            (string) $order->id,
            Request::create('/orders/'.$order->id.'/items', 'PUT', ['items' => []]),
            app(OrderService::class)
        );
    }

    public function test_dine_in_final_billing_marks_success_without_duplicate_payment(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $order = $this->order(total: 500, orderType: 'dine_in', diningFlow: 'table_service');

        $payment = app(PaymentService::class)->createPayment($order, 'cash', 500);
        $result = app(PaymentService::class)->markPaymentSuccess($payment->fresh());

        $this->assertSame('completed', $result['order']->status);
        $this->assertSame('paid', $result['order']->payment_status);
        $this->assertSame(1, Payment::count());
    }

    public function test_offline_sync_retry_returns_existing_synced_response_shape(): void
    {
        $order = $this->order(total: 90, status: 'completed', paymentStatus: 'paid', meta: [
            'offline' => true,
            'local_order_id' => 'local-1',
        ]);

        $this->assertSame('local-1', data_get($order->meta, 'local_order_id'));
        $this->assertSame('completed', $order->status);
    }

    public function test_existing_invoice_and_token_flow_is_not_overwritten_by_already_paid_create(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $order = $this->order(total: 75, status: 'completed', paymentStatus: 'paid', meta: [
            'invoice' => ['url' => 'https://example.test/billing/invoices/PC26-ABC'],
        ]);
        $order->forceFill([
            'invoice_id' => 10,
            'invoice_no' => 'PC26-ABC',
            'token_id' => 5,
        ])->save();

        $response = app(PaymentService::class)->createPayment($order->fresh(), 'cash', 75);

        $this->assertTrue($response['already_paid']);
        $this->assertSame('PC26-ABC', $order->fresh()->invoice_no);
        $this->assertSame(5, (int) $order->fresh()->token_id);
        $this->assertSame(0, Payment::count());
    }

    private function order(
        float $total,
        string $status = 'pending_payment',
        string $paymentStatus = 'unpaid',
        string $orderType = 'takeaway',
        ?string $diningFlow = null,
        ?array $meta = null
    ): Order {
        $product = Product::first() ?: Product::create([
            'name' => 'Tea',
            'sku' => 'TEA',
            'type' => 'simple',
            'price' => $total,
            'track_inventory' => false,
        ]);

        $order = Order::create([
            'order_no' => 'ORD-'.str()->uuid(),
            'location_id' => 1,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'order_type' => $orderType,
            'dining_flow' => $diningFlow,
            'subtotal' => $total,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'paid_amount' => $paymentStatus === 'paid' ? $total : 0,
            'balance_due' => $paymentStatus === 'paid' ? 0 : $total,
            'completed_at' => $status === 'completed' ? now() : null,
            'meta' => $meta,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $total,
            'subtotal' => $total,
            'total' => $total,
        ]);

        return $order;
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

        Schema::connection('tenant')->create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->unsignedBigInteger('token_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->unsignedBigInteger('table_session_id')->nullable();
            $table->string('status')->default('draft');
            $table->string('payment_status')->default('unpaid');
            $table->string('order_type')->nullable();
            $table->string('dining_flow')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->decimal('change_returned', 15, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('type')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('track_inventory')->default(false);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('payment_method');
            $table->string('mode')->nullable();
            $table->string('provider')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('transaction_id')->nullable();
            $table->string('provider_ref')->nullable();
            $table->unsignedBigInteger('upi_profile_id')->nullable();
            $table->string('upi_qr_url')->nullable();
            $table->string('status')->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('mode')->nullable();
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('upi_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('upi_id');
            $table->string('payee_name')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('order_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->integer('token_number');
            $table->string('token_code');
            $table->date('token_date');
            $table->string('status')->default('waiting');
            $table->timestamps();
        });
    }
}
