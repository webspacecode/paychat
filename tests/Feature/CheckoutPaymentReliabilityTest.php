<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Tenant\OrderController;
use App\Http\Controllers\Api\Tenant\PaymentController;
use App\Models\Invoice;
use App\Models\OfflineOrderSync;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\OrderToken;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethodCorrection;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\Recipe;
use App\Models\Tenant\UpiProfile;
use App\Services\InvoiceService;
use App\Services\KitchenBatchService;
use App\Services\OfflineOrderSyncService;
use App\Services\OrderKitchenDispatchService;
use App\Services\Orders\OrderService;
use App\Services\PaymentMethodCorrectionService;
use App\Services\Payments\PaymentService;
use App\Services\SelfPosOrderService;
use App\Services\TableSessionService;
use App\Services\TokenService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
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
        Config::set('database.connections.mysql', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('tenant');
        DB::purge('mysql');
        DB::setDefaultConnection('tenant');
        DB::connection('tenant')->reconnect();
        DB::connection('mysql')->reconnect();
        Config::set('broadcasting.default', 'null');

        $this->createCentralSchema();
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
            public function generate($order, $tenant, $industry, $paper, bool $includeCustomerInfo = false)
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
        $this->assertTrue($payload['success']);
        $this->assertSame('success', $payload['payment_status']);
        $this->assertTrue($payload['already_paid']);
        $this->assertSame('pending', $payload['side_effects']['invoice']);
        $this->assertSame('pending', $payload['side_effects']['token']);
        $this->assertFalse($payload['invoice_generated']);
    }

    public function test_completed_order_item_update_returns_conflict(): void
    {
        $order = $this->order(total: 120, status: 'completed', paymentStatus: 'paid');

        $response = app(OrderController::class)->updateItems(
            'demo',
            (string) $order->id,
            Request::create('/orders/'.$order->id.'/items', 'PUT', ['items' => []]),
            app(OrderService::class)
        );

        $payload = $response->getData(true);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame('order_locked', $payload['error_code']);
        $this->assertSame('completed', $payload['order_status']);
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

    public function test_payment_method_correction_updates_payment_invoice_report_and_audit(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        PaymentMethod::create(['type' => 'upi', 'enabled' => true]);
        $profile = UpiProfile::create([
            'label' => 'Main UPI',
            'upi_id' => 'store@upi',
            'location_id' => 1,
            'is_active' => true,
        ]);
        $order = $this->order(total: 250, status: 'completed', paymentStatus: 'paid');
        $order->forceFill(['business_date' => now()->toDateString()])->save();
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 250,
            'status' => 'success',
        ]);
        Invoice::on('mysql')->create([
            'tenant_id' => 23,
            'order_id' => $order->id,
            'uuid' => 'PC26-CORRECT-'.$order->id,
            'industry' => 'cafe',
            'paper_size' => '80mm',
            'order_data' => [
                'id' => $order->id,
                'payments' => [[
                    'id' => $payment->id,
                    'payment_method' => 'cash',
                    'amount' => 250,
                    'status' => 'success',
                ]],
            ],
        ]);

        $result = app(PaymentMethodCorrectionService::class)->correct($order, [
            'payment_id' => $payment->id,
            'new_method' => 'upi',
            'upi_profile_id' => $profile->id,
            'reason' => 'Customer paid using UPI but cash was selected.',
        ], null, 'payment-correction-test-1');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['changed']);
        $this->assertSame('updated', $result['side_effects']['invoice_snapshot']);
        $this->assertSame('refreshed', $result['side_effects']['reports']);
        $this->assertSame('upi', $payment->fresh()->payment_method);
        $this->assertSame($profile->id, $payment->fresh()->upi_profile_id);
        $this->assertSame(1, PaymentMethodCorrection::count());

        $invoicePayment = Invoice::on('mysql')->first()->order_data['payments'][0];
        $this->assertSame('upi', $invoicePayment['payment_method']);
        $this->assertSame($profile->id, $invoicePayment['upi_profile_id']);

        $this->assertDatabaseHas('report_payment_breakdowns', [
            'tenant_id' => 23,
            'location_id' => 1,
            'payment_method' => 'upi',
        ], 'tenant');
    }

    public function test_payment_method_correction_blocks_multiple_successful_payments_without_selection(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        PaymentMethod::create(['type' => 'upi', 'enabled' => true]);
        $order = $this->order(total: 250, status: 'completed', paymentStatus: 'paid');
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 100,
            'status' => 'success',
        ]);
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'amount' => 150,
            'status' => 'success',
        ]);

        $this->expectException(ValidationException::class);

        app(PaymentMethodCorrectionService::class)->correct($order, [
            'new_method' => 'cash',
            'reason' => 'Correction requires explicit split payment selection.',
        ], null, 'payment-correction-test-2');
    }

    public function test_payment_method_correction_is_idempotent(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        PaymentMethod::create(['type' => 'phonepe', 'enabled' => true]);
        $order = $this->order(total: 120, status: 'completed', paymentStatus: 'paid');
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 120,
            'status' => 'success',
        ]);

        $payload = [
            'payment_id' => $payment->id,
            'new_method' => 'phonepe',
            'reason' => 'Customer paid through PhonePe.',
        ];

        $first = app(PaymentMethodCorrectionService::class)->correct($order, $payload, null, 'payment-correction-test-3');
        $second = app(PaymentMethodCorrectionService::class)->correct($order, $payload, null, 'payment-correction-test-3');

        $this->assertTrue($first['changed']);
        $this->assertSame($first['correction']['id'], $second['correction']['id']);
        $this->assertSame('phonepe', $payment->fresh()->payment_method);
        $this->assertSame(1, PaymentMethodCorrection::count());
    }

    public function test_kot_send_batches_fresh_items_once(): void
    {
        $order = $this->tableServiceOrder();
        $service = app(KitchenBatchService::class);

        $batch = $service->sendFreshItems($order, KitchenBatchService::CHANNEL_BOARD);

        $this->assertSame('board', $batch->dispatch_channel);
        $this->assertSame(1, $batch->items()->count());
        $this->assertSame('sent', $order->items()->first()->fresh()->kitchen_status);

        $this->expectException(ValidationException::class);
        $service->sendFreshItems($order->fresh(), KitchenBatchService::CHANNEL_BOARD);
    }

    public function test_print_channel_creates_batch_without_board_dispatch(): void
    {
        $order = $this->tableServiceOrder();
        $service = app(KitchenBatchService::class);

        $batch = $service->sendFreshItems($order, KitchenBatchService::CHANNEL_PRINT);

        $this->assertSame('print', $batch->dispatch_channel);
        $this->assertFalse($service->shouldDispatchToBoard($batch->dispatch_channel));
    }

    public function test_inline_kitchen_board_channel_still_broadcasts_to_order_board(): void
    {
        DB::connection('tenant')->table('settings')->insert([
            'setting_key' => 'kitchen_operation_mode',
            'value' => KitchenBatchService::MODE_INLINE,
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = $this->tableServiceOrder();
        $service = app(KitchenBatchService::class);

        $boardBatch = $service->sendFreshItems($order, KitchenBatchService::CHANNEL_BOARD);

        $this->assertSame(KitchenBatchService::MODE_INLINE, $service->operationMode());
        $this->assertTrue($service->shouldBroadcastToKds($boardBatch));
    }

    public function test_waiting_kot_batch_can_be_cancelled_safely(): void
    {
        $order = $this->tableServiceOrder();
        $service = app(KitchenBatchService::class);
        $batch = $service->sendFreshItems($order, KitchenBatchService::CHANNEL_PRINT);

        $cancelled = $service->cancelBatch($batch);

        $this->assertSame('cancelled', $cancelled->status);
        $item = $order->items()->first()->fresh();
        $this->assertNull($item->kitchen_batch_id);
        $this->assertSame('pending', $item->kitchen_status);
    }

    public function test_payment_success_reports_recipe_stock_shortage_as_validation_error(): void
    {
        $rawProduct = Product::create([
            'name' => 'Flour',
            'sku' => 'FLOUR',
            'type' => 'raw',
            'price' => 0,
            'track_inventory' => true,
        ]);
        $recipeProduct = Product::create([
            'name' => 'Cake Slice',
            'sku' => 'CAKE-SLICE',
            'type' => 'recipe',
            'price' => 100,
            'track_inventory' => true,
        ]);
        $recipe = Recipe::create([
            'product_id' => $recipeProduct->id,
            'location_id' => 1,
        ]);
        $recipe->items()->create([
            'raw_product_id' => $rawProduct->id,
            'quantity' => 2,
            'unit' => 'kg',
        ]);
        ProductInventory::create([
            'product_id' => $rawProduct->id,
            'location_id' => 1,
            'quantity' => 1,
        ]);

        $order = Order::create([
            'order_no' => 'ORD-'.str()->uuid(),
            'location_id' => 1,
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'order_type' => 'dine_in',
            'dining_flow' => 'table_service',
            'subtotal' => 100,
            'tax' => 0,
            'discount' => 0,
            'total' => 100,
            'paid_amount' => 0,
            'balance_due' => 100,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $recipeProduct->id,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
            'total' => 100,
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 100,
            'status' => 'pending',
        ]);

        try {
            app(PaymentService::class)->markPaymentSuccess($payment->fresh());
            $this->fail('Expected insufficient stock to fail as a validation error.');
        } catch (ValidationException $e) {
            $message = $e->errors()['stock'][0] ?? '';

            $this->assertStringContainsString('Insufficient stock for raw product Flour', $message);
            $this->assertStringContainsString('Cake Slice', $message);
        }

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_offline_sync_retry_returns_existing_synced_response_shape(): void
    {
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = Product::create([
            'name' => 'Coffee',
            'sku' => 'COF',
            'type' => 'simple',
            'price' => 90,
            'track_inventory' => false,
        ]);

        OfflineOrderSync::create([
            'tenant_id' => 23,
            'local_order_id' => 'local-1',
            'status' => 'processing',
            'payload' => ['local_order_id' => 'local-1', 'stale' => true],
        ]);
        OfflineOrderSync::where('tenant_id', 23)->where('local_order_id', 'local-1')->update([
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);

        $response = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), [
            'local_order_id' => 'local-1',
            'location_id' => 1,
            'order_type' => 'takeaway',
            'offline_created_at' => now()->subMinutes(30)->toISOString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'totals' => [
                'subtotal' => 90,
                'tax_total' => 0,
                'discount_total' => 0,
                'grand_total' => 90,
                'paid_amount' => 90,
                'balance_amount' => 0,
            ],
            'payment' => [
                'method' => 'cash',
                'amount' => 90,
                'reference' => 'offline-ref-1',
            ],
        ]);

        $sync = OfflineOrderSync::where('tenant_id', 23)->where('local_order_id', 'local-1')->first();

        $this->assertTrue($response['success']);
        $this->assertSame('synced', $sync->status);
        $this->assertSame(1, Order::where('status', 'completed')->count());
    }

    public function test_offline_sync_creates_invoice_when_missing(): void
    {
        $this->bindOfflineInvoiceService();
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();

        $response = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $this->offlinePayload('local-invoice-1', $product));

        $order = Order::findOrFail($response['backend_order_id']);

        $this->assertTrue($response['success']);
        $this->assertSame(1, Invoice::on('mysql')->count());
        $this->assertNotNull($order->invoice_id);
        $this->assertNotNull($order->invoice_no);
        $this->assertSame($order->invoice_no, $response['invoice_number']);
        $this->assertSame($order->invoice_no, $response['invoice_uuid']);
        $this->assertSame(data_get($order->meta, 'invoice.url'), $response['invoice_url']);
    }

    public function test_offline_sync_preserves_offline_invoice_number(): void
    {
        $this->bindOfflineInvoiceService();
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();

        $response = app(OfflineOrderSyncService::class)->sync(
            app('currentTenant'),
            $this->offlinePayload('local-preserve-invoice-1', $product, 'PC-OFF-000001')
        );

        $order = Order::findOrFail($response['backend_order_id']);
        $invoice = Invoice::on('mysql')->where('uuid', 'PC-OFF-000001')->firstOrFail();

        $this->assertTrue($response['success']);
        $this->assertSame('PC-OFF-000001', $invoice->uuid);
        $this->assertSame('PC-OFF-000001', $order->invoice_no);
        $this->assertSame('PC-OFF-000001', data_get($order->meta, 'invoice.number'));
        $this->assertSame('PC-OFF-000001', $response['invoice_number']);
        $this->assertSame($invoice->id, $response['invoice_id']);
    }

    public function test_offline_sync_reuses_existing_invoice(): void
    {
        $this->bindOfflineInvoiceService();
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();

        $first = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $this->offlinePayload('local-invoice-2', $product));
        $second = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $this->offlinePayload('local-invoice-2', $product));

        $this->assertSame(1, Invoice::on('mysql')->count());
        $this->assertSame($first['backend_order_id'], $second['backend_order_id']);
        $this->assertSame($first['invoice_number'], $second['invoice_number']);
        $this->assertSame($first['invoice_id'], $second['invoice_id']);
    }

    public function test_duplicate_offline_sync_does_not_create_duplicate_invoice(): void
    {
        $this->bindOfflineInvoiceService();
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();
        $payload = $this->offlinePayload('local-invoice-3', $product);

        app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $payload);
        app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $payload);

        $this->assertSame(1, Invoice::on('mysql')->count());
        $this->assertSame(1, Order::count());
        $this->assertSame(1, Payment::count());
    }

    public function test_duplicate_offline_sync_with_preserved_invoice_returns_cached_response(): void
    {
        $this->bindOfflineInvoiceService();
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();
        $payload = $this->offlinePayload('local-preserve-invoice-2', $product, 'PC-OFF-000002');

        $first = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $payload);
        $second = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $payload);

        $this->assertSame(1, Invoice::on('mysql')->where('uuid', 'PC-OFF-000002')->count());
        $this->assertSame(1, Order::count());
        $this->assertSame(1, Payment::count());
        $this->assertSame($first['backend_order_id'], $second['backend_order_id']);
        $this->assertSame('PC-OFF-000002', $second['invoice_number']);
        $this->assertSame($first['invoice_id'], $second['invoice_id']);
        $this->assertSame($first['payment_id'], $second['payment_id']);
        $this->assertSame($first['token_id'], $second['token_id']);
    }

    public function test_different_offline_order_cannot_reuse_existing_offline_invoice_number(): void
    {
        $this->bindOfflineInvoiceService();
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();

        app(OfflineOrderSyncService::class)->sync(
            app('currentTenant'),
            $this->offlinePayload('local-preserve-conflict-1', $product, 'PC-OFF-CONFLICT')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Offline invoice number already exists. Cannot change customer-facing invoice number.');

        try {
            app(OfflineOrderSyncService::class)->sync(
                app('currentTenant'),
                $this->offlinePayload('local-preserve-conflict-2', $product, 'PC-OFF-CONFLICT')
            );
        } finally {
            $this->assertSame(1, Invoice::on('mysql')->where('uuid', 'PC-OFF-CONFLICT')->count());
            $this->assertSame(1, Order::count());
            $this->assertSame(1, Payment::count());
        }
    }

    public function test_invalid_offline_invoice_number_fails_without_creating_order_payment_or_invoice(): void
    {
        $this->bindOfflineInvoiceService();
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid offline invoice number.');

        try {
            app(OfflineOrderSyncService::class)->sync(
                app('currentTenant'),
                $this->offlinePayload('local-invalid-invoice', $product, 'BAD NUMBER!')
            );
        } finally {
            $this->assertSame(0, Invoice::on('mysql')->count());
            $this->assertSame(0, Order::count());
            $this->assertSame(0, Payment::count());
        }
    }

    public function test_invoice_generation_failure_does_not_duplicate_order_or_payment(): void
    {
        $this->bindOfflineInvoiceService(fail: true);
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $product = $this->offlineProduct();
        $payload = $this->offlinePayload('local-invoice-failure', $product);

        $first = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $payload);
        $second = app(OfflineOrderSyncService::class)->sync(app('currentTenant'), $payload);

        $this->assertTrue($first['success']);
        $this->assertNull($first['invoice_number']);
        $this->assertSame($first['backend_order_id'], $second['backend_order_id']);
        $this->assertSame($first['payment_id'], $second['payment_id']);
        $this->assertNull($second['invoice_number']);
        $this->assertSame(0, Invoice::on('mysql')->count());
        $this->assertSame(1, Order::count());
        $this->assertSame(1, Payment::count());
        $this->assertSame('completed', Order::first()->status);
        $this->assertSame('paid', Order::first()->payment_status);
    }

    public function test_online_checkout_behavior_remains_unchanged(): void
    {
        $order = $this->order(total: 180, orderType: 'dine_in', diningFlow: 'table_service');
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'amount' => 180,
            'status' => 'pending',
        ]);

        app()->instance(InvoiceService::class, new class extends InvoiceService {
            public function generate($order, $tenant, $industry, $paper, bool $includeCustomerInfo = false)
            {
                throw new \RuntimeException('Online invoice failure remains a side effect');
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
        $this->assertTrue($payload['success']);
        $this->assertSame('success', $payload['payment_status']);
        $this->assertSame('failed', $payload['side_effects']['invoice']);
        $this->assertArrayHasKey('invoice_generated', $payload);
        $this->assertSame('completed', $payment->fresh()->order->status);
        $this->assertSame('paid', $payment->fresh()->order->payment_status);
    }

    public function test_payment_success_survives_invoice_failure_after_payment_commit(): void
    {
        $order = $this->order(total: 180, orderType: 'dine_in', diningFlow: 'table_service');
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'amount' => 180,
            'status' => 'pending',
        ]);

        app()->instance(InvoiceService::class, new class extends InvoiceService {
            public function generate($order, $tenant, $industry, $paper, bool $includeCustomerInfo = false)
            {
                throw new \RuntimeException('Simulated slow invoice failure');
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
        $this->assertTrue($payload['success']);
        $this->assertSame('success', $payload['payment_status']);
        $this->assertSame('failed', $payload['side_effects']['invoice']);
        $this->assertSame('completed', $payment->fresh()->order->status);
        $this->assertSame('paid', $payment->fresh()->order->payment_status);
    }

    public function test_payment_success_survives_token_dispatch_failure_after_payment_commit(): void
    {
        $order = $this->order(total: 180);
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'amount' => 180,
            'status' => 'pending',
        ]);

        app()->instance(OrderKitchenDispatchService::class, new class(app(TokenService::class)) extends OrderKitchenDispatchService {
            public function ensureTokenAndDispatchWhenReady(Order $order, string $reason): ?OrderToken
            {
                throw new \RuntimeException('Simulated token dispatch failure');
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
        $this->assertTrue($payload['success']);
        $this->assertSame('failed', $payload['side_effects']['token']);
        $this->assertSame('failed', $payload['side_effects']['broadcast']);
        $this->assertSame('completed', $payment->fresh()->order->status);
        $this->assertSame('paid', $payment->fresh()->order->payment_status);
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

    public function test_self_pos_cash_submit_creates_pending_payment_and_token_without_invoice_or_completion(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $order = $this->order(total: 150, status: 'draft');

        $first = app(SelfPosOrderService::class)->submit($order, [
            'payment_method' => 'cash',
            'amount' => 150,
            'customer' => ['name' => 'Walk In', 'phone' => '9999999999'],
        ]);
        $second = app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'cash',
            'amount' => 150,
        ]);

        $fresh = $order->fresh();
        $this->assertTrue($first['requires_biller_confirmation']);
        $this->assertSame('pending_payment', $fresh->status);
        $this->assertSame('unpaid', $fresh->payment_status);
        $this->assertNull($fresh->invoice_id);
        $this->assertNull($fresh->invoice_no);
        $this->assertSame(1, Payment::where('payment_method', 'cash')->where('status', 'pending')->count());
        $this->assertSame(1, OrderToken::count());
        $this->assertSame(OrderToken::first()->id, $fresh->token_id);
        $this->assertSame(Payment::first()->id, $second['payment']->id);
    }

    public function test_self_pos_biller_confirmation_completes_order_and_generates_invoice(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $this->bindSelfPosInvoiceService();

        $order = $this->order(total: 175, status: 'draft');
        app(SelfPosOrderService::class)->submit($order, [
            'payment_method' => 'cash',
            'amount' => 175,
        ]);

        $result = app(SelfPosOrderService::class)->confirmPayment($order->fresh(), 'cash');
        $fresh = $order->fresh();

        $this->assertTrue($result['success']);
        $this->assertSame('completed', $fresh->status);
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('success', Payment::first()->fresh()->status);
        $this->assertNotNull($fresh->invoice_id);
        $this->assertSame('PC26-SELF-'.$fresh->id, $fresh->invoice_no);
    }

    public function test_self_pos_upi_submit_uses_backend_payable_amount_when_browser_total_is_stale(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create([
            'type' => 'upi',
            'mode' => 'personal',
            'enabled' => true,
            'config' => ['upi_id' => 'store@upi', 'name' => 'Store'],
        ]);
        $order = $this->order(total: 220, status: 'pending_payment');

        $result = app(SelfPosOrderService::class)->submit($order, [
            'payment_method' => 'upi',
            'amount' => 260,
        ]);

        $payment = Payment::first();
        $fresh = $order->fresh();

        $this->assertTrue($result['success']);
        $this->assertSame('pending_payment', $fresh->status);
        $this->assertSame('unpaid', $fresh->payment_status);
        $this->assertSame('upi', $payment->payment_method);
        $this->assertSame('pending', $payment->status);
        $this->assertSame(220.0, (float) $payment->amount);
        $this->assertNotNull($payment->upi_qr_url);
        $this->assertNull($fresh->invoice_id);
    }

    public function test_self_pos_upi_submit_reuses_existing_pending_payment_attempt(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create([
            'type' => 'upi',
            'mode' => 'personal',
            'enabled' => true,
            'config' => ['upi_id' => 'store@upi', 'name' => 'Store'],
        ]);
        $order = $this->order(total: 220, status: 'pending_payment');
        $payment = app(PaymentService::class)->createPayment($order, 'upi', 220);

        $result = app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'upi',
            'payment_id' => $payment->id,
            'amount' => 220,
        ]);

        $fresh = $order->fresh();

        $this->assertTrue($result['success']);
        $this->assertSame($payment->id, $result['payment']->id);
        $this->assertSame(1, Payment::count());
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $fresh->status);
        $this->assertSame('unpaid', $fresh->payment_status);
        $this->assertSame($payment->id, data_get($fresh->meta, 'self_pos.payment_id'));
        $this->assertTrue(data_get($fresh->meta, 'self_pos.customer_submitted_after_upi'));
        $this->assertNotNull($result['kitchen_qr']);
        $this->assertNull($fresh->invoice_id);
    }

    public function test_self_pos_table_qr_submit_creates_active_table_session_and_links_order(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $this->tableResource(31, 'T31');
        $order = $this->order(total: 260, status: 'draft', orderType: 'dine_in', diningFlow: 'table_service');
        $order->update(['table_id' => 31]);

        $result = app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'cash',
            'amount' => 260,
        ]);

        $fresh = $order->fresh(['tableSession', 'kitchenBatches']);

        $this->assertTrue($result['success']);
        $this->assertSame('pending_payment', $fresh->status);
        $this->assertSame('unpaid', $fresh->payment_status);
        $this->assertSame('dine_in', $fresh->order_type);
        $this->assertSame('table_service', $fresh->dining_flow);
        $this->assertSame(31, (int) $fresh->table_id);
        $this->assertNotNull($fresh->table_session_id);
        $this->assertSame('active', $fresh->tableSession->status);
        $this->assertSame($fresh->id, (int) $fresh->tableSession->order_id);
        $this->assertSame('occupied', DB::connection('tenant')->table('resources')->where('id', 31)->value('status'));
        $this->assertSame(1, DB::connection('tenant')->table('table_sessions')->where('table_id', 31)->where('status', 'active')->count());
        $this->assertSame($fresh->table_session_id, DB::connection('tenant')->table('kitchen_batches')->value('table_session_id'));
    }

    public function test_self_pos_table_qr_submit_is_idempotent_for_table_session(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create(['type' => 'cash', 'enabled' => true]);
        $this->tableResource(32, 'T32');
        $order = $this->order(total: 180, status: 'draft', orderType: 'dine_in', diningFlow: 'table_service');
        $order->update(['table_id' => 32]);

        $first = app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'cash',
            'amount' => 180,
        ]);
        $second = app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'cash',
            'amount' => 180,
        ]);

        $fresh = $order->fresh();

        $this->assertTrue($first['success']);
        $this->assertTrue($second['success']);
        $this->assertSame(1, DB::connection('tenant')->table('table_sessions')->where('table_id', 32)->where('status', 'active')->count());
        $this->assertSame(1, Payment::where('payment_method', 'cash')->where('status', 'pending')->count());
        $this->assertSame($fresh->table_session_id, $second['order']['table_session_id']);
    }

    public function test_self_pos_table_qr_upi_submit_keeps_payment_pending_and_links_table_session(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create([
            'type' => 'upi',
            'mode' => 'personal',
            'enabled' => true,
            'config' => ['upi_id' => 'store@upi', 'name' => 'Store'],
        ]);
        $this->tableResource(33, 'T33');
        $order = $this->order(total: 220, status: 'pending_payment', orderType: 'dine_in', diningFlow: 'table_service');
        $order->update(['table_id' => 33]);
        $payment = app(PaymentService::class)->createPayment($order, 'upi', 220);

        $result = app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'upi',
            'payment_id' => $payment->id,
            'amount' => 220,
        ]);

        $fresh = $order->fresh(['tableSession']);

        $this->assertTrue($result['success']);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $fresh->status);
        $this->assertSame('unpaid', $fresh->payment_status);
        $this->assertNotNull($fresh->table_session_id);
        $this->assertSame('active', $fresh->tableSession->status);
        $this->assertTrue(data_get($fresh->meta, 'self_pos.customer_submitted_after_upi'));
        $this->assertSame('processed_pending_verification', $result['payment_display_status']);
    }

    public function test_self_pos_upi_submit_rejects_payment_id_from_another_order(): void
    {
        PaymentMethod::create([
            'type' => 'upi',
            'mode' => 'personal',
            'enabled' => true,
            'config' => ['upi_id' => 'store@upi', 'name' => 'Store'],
        ]);
        $order = $this->order(total: 220, status: 'pending_payment');
        $other = $this->order(total: 220, status: 'pending_payment');
        $payment = app(PaymentService::class)->createPayment($other, 'upi', 220);

        $this->expectException(ValidationException::class);

        app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'upi',
            'payment_id' => $payment->id,
            'amount' => 220,
        ]);
    }

    public function test_self_pos_biller_confirmation_marks_reused_upi_payment_success_and_generates_invoice(): void
    {
        \App\Models\Tenant\Setting::set('token_system_enabled', true, 'boolean');
        PaymentMethod::create([
            'type' => 'upi',
            'mode' => 'personal',
            'enabled' => true,
            'config' => ['upi_id' => 'store@upi', 'name' => 'Store'],
        ]);
        $this->bindSelfPosInvoiceService();
        $order = $this->order(total: 220, status: 'pending_payment');
        $payment = app(PaymentService::class)->createPayment($order, 'upi', 220);
        app(SelfPosOrderService::class)->submit($order->fresh(), [
            'payment_method' => 'upi',
            'payment_id' => $payment->id,
            'amount' => 220,
        ]);

        $result = app(SelfPosOrderService::class)->confirmPayment($order->fresh(), 'upi');
        $fresh = $order->fresh();

        $this->assertTrue($result['success']);
        $this->assertSame('success', $payment->fresh()->status);
        $this->assertSame('completed', $fresh->status);
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('PC26-SELF-'.$fresh->id, $fresh->invoice_no);
        $this->assertSame(1, Payment::count());
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

    private function bindSelfPosInvoiceService(): void
    {
        app()->instance(InvoiceService::class, new class extends InvoiceService {
            public function generate($order, $tenant, $industry, $paper, bool $includeCustomerInfo = false)
            {
                $orderId = data_get($order, 'id');
                $invoiceNo = 'PC26-SELF-'.$orderId;

                $invoice = Invoice::on('mysql')->create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $orderId,
                    'uuid' => $invoiceNo,
                    'industry' => $industry,
                    'paper_size' => $paper,
                    'order_data' => $order,
                ]);

                $tenantOrder = Order::findOrFail($orderId);
                $meta = $tenantOrder->meta ?? [];
                $meta['invoice'] = [
                    'id' => $invoice->id,
                    'number' => $invoiceNo,
                    'url' => url("/billing/invoices/{$invoiceNo}"),
                ];

                $tenantOrder->update([
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoiceNo,
                    'meta' => $meta,
                ]);

                return ['url' => url("/billing/invoices/{$invoiceNo}")];
            }
        });
    }

    private function tableServiceOrder(): Order
    {
        $this->tableResource(10, 'T1', 'occupied');

        DB::connection('tenant')->table('table_sessions')->insert([
            'id' => 20,
            'location_id' => 1,
            'primary_table_id' => 10,
            'table_id' => 10,
            'status' => 'active',
            'guest_count' => 1,
            'opened_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = $this->order(total: 120, orderType: 'dine_in', diningFlow: 'table_service');
        $order->update([
            'table_id' => 10,
            'table_session_id' => 20,
            'guest_count' => 1,
        ]);

        return $order->fresh(['items']);
    }

    private function tableResource(int $id, string $code, string $status = 'available'): void
    {
        DB::connection('tenant')->table('resources')->insert([
            'id' => $id,
            'location_id' => 1,
            'name' => $code,
            'code' => $code,
            'type' => 'table',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function offlineProduct(): Product
    {
        return Product::create([
            'name' => 'Coffee',
            'sku' => 'COF-'.str()->random(6),
            'type' => 'simple',
            'price' => 90,
            'track_inventory' => false,
        ]);
    }

    private function offlinePayload(string $localOrderId, Product $product, ?string $offlineInvoiceNumber = null): array
    {
        $payload = [
            'local_order_id' => $localOrderId,
            'location_id' => 1,
            'order_type' => 'takeaway',
            'offline_created_at' => now()->subMinutes(30)->toISOString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'totals' => [
                'subtotal' => 90,
                'tax_total' => 0,
                'discount_total' => 0,
                'grand_total' => 90,
                'paid_amount' => 90,
                'balance_amount' => 0,
            ],
            'payment' => [
                'method' => 'cash',
                'amount' => 90,
                'reference' => 'offline-ref-'.$localOrderId,
            ],
        ];

        if ($offlineInvoiceNumber !== null) {
            $payload['invoice'] = [
                'offline_invoice_number' => $offlineInvoiceNumber,
            ];
        }

        return $payload;
    }

    private function bindOfflineInvoiceService(bool $fail = false): void
    {
        app()->instance(InvoiceService::class, new class($fail) extends InvoiceService {
            public function __construct(private bool $fail)
            {
            }

            public function generate($order, $tenant, $industry, $paper, bool $includeCustomerInfo = false)
            {
                if ($this->fail) {
                    throw new \RuntimeException('Simulated offline invoice failure');
                }

                $orderId = data_get($order, 'id');
                return $this->generateUsingNumber($order, $tenant, $industry, $paper, 'PC26-O'.$orderId);
            }

            public function generateWithPreferredInvoiceNumber($order, $tenant, $industry, $paper, string $preferredInvoiceNumber, bool $includeCustomerInfo = false)
            {
                if ($this->fail) {
                    throw new \RuntimeException('Simulated offline invoice failure');
                }

                return $this->generateUsingNumber($order, $tenant, $industry, $paper, $preferredInvoiceNumber);
            }

            private function generateUsingNumber($order, $tenant, $industry, $paper, string $invoiceNumber)
            {
                $orderId = data_get($order, 'id');
                $invoice = Invoice::on('mysql')
                    ->where('tenant_id', $tenant->id)
                    ->where('order_id', $orderId)
                    ->first();

                if ($invoice && $invoice->uuid !== $invoiceNumber) {
                    throw new \RuntimeException('Offline invoice number already exists. Cannot change customer-facing invoice number.');
                }

                if (! $invoice) {
                    $invoice = Invoice::on('mysql')->where('uuid', $invoiceNumber)->first();

                    if ($invoice) {
                        $invoiceLocalOrderId = data_get($invoice->order_data, 'meta.local_order_id');
                        $incomingLocalOrderId = data_get($order, 'meta.local_order_id');

                        if (
                            (int) $invoice->tenant_id !== (int) $tenant->id ||
                            ((int) $invoice->order_id !== (int) $orderId && (! $invoiceLocalOrderId || $invoiceLocalOrderId !== $incomingLocalOrderId))
                        ) {
                            throw new \RuntimeException('Offline invoice number already exists. Cannot change customer-facing invoice number.');
                        }
                    }
                }

                if (! $invoice) {
                    $invoice = Invoice::on('mysql')->create([
                        'tenant_id' => $tenant->id,
                        'order_id' => $orderId,
                        'uuid' => $invoiceNumber,
                        'industry' => $industry,
                        'paper_size' => $paper,
                        'order_data' => $order,
                    ]);
                }

                $url = url("/billing/invoices/{$invoice->uuid}");
                $tenantOrder = Order::findOrFail($orderId);
                $meta = $tenantOrder->meta ?? [];
                $meta['invoice'] = [
                    'id' => $invoice->id,
                    'number' => $invoice->uuid,
                    'url' => $url,
                ];

                $tenantOrder->update([
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->uuid,
                    'meta' => $meta,
                ]);

                return ['url' => $url];
            }
        });
    }

    private function createTenantSchema(): void
    {
        Schema::connection('tenant')->create('offline_order_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('local_order_id', 100);
            $table->unsignedBigInteger('backend_order_id')->nullable();
            $table->string('status', 30)->default('processing');
            $table->json('payload');
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'local_order_id']);
        });

        Schema::connection('tenant')->create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        Schema::connection('tenant')->create('idempotency_requests', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 100);
            $table->char('idempotency_key_hash', 64);
            $table->char('request_hash', 64);
            $table->string('status', 20)->default('processing');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->string('resource_type', 100)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->dateTime('locked_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['scope', 'idempotency_key_hash']);
        });

        Schema::connection('tenant')->create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('table_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('primary_table_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('guest_count')->nullable();
            $table->timestamp('opened_at')->nullable();
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
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->unsignedBigInteger('table_session_id')->nullable();
            $table->unsignedInteger('guest_count')->nullable();
            $table->date('business_date')->nullable();
            $table->string('status')->default('draft');
            $table->string('payment_status')->default('unpaid');
            $table->string('order_type')->nullable();
            $table->string('delivery_channel')->nullable();
            $table->string('delivery_channel_label')->nullable();
            $table->string('external_order_reference')->nullable();
            $table->string('dining_flow')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->decimal('change_returned', 15, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('ordered_at')->nullable();
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
            $table->unsignedBigInteger('kitchen_batch_id')->nullable();
            $table->string('kitchen_status')->nullable();
            $table->timestamp('sent_to_kitchen_at')->nullable();
            $table->string('item_status')->nullable();
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('product_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id');
            $table->decimal('quantity', 12, 4)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'location_id']);
        });

        Schema::connection('tenant')->create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('raw_product_id');
            $table->decimal('quantity', 12, 4);
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('from_location_id')->nullable();
            $table->unsignedBigInteger('to_location_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('quantity', 12, 4);
            $table->string('type');
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
            $table->string('status')->default('waiting');
            $table->string('dispatch_channel')->default('board');
            $table->timestamp('sent_at')->nullable();
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

        Schema::connection('tenant')->create('payment_method_corrections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_id');
            $table->string('old_payment_method', 50);
            $table->string('new_payment_method', 50);
            $table->unsignedBigInteger('old_upi_profile_id')->nullable();
            $table->unsignedBigInteger('new_upi_profile_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->unsignedBigInteger('corrected_by')->nullable();
            $table->timestamp('corrected_at')->nullable();
            $table->char('idempotency_key_hash', 64)->nullable();
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

        Schema::connection('tenant')->create('report_daily_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('date');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('net_sales', 12, 2)->default(0);
            $table->decimal('avg_order_value', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'location_id', 'date']);
        });

        Schema::connection('tenant')->create('report_payment_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('date');
            $table->string('payment_method');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'location_id', 'date', 'payment_method'], 'report_payments_identity_unique');
        });

        Schema::connection('tenant')->create('report_top_products_daily', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->integer('quantity_sold')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->integer('rank')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'location_id', 'date', 'product_id'], 'report_products_identity_unique');
        });

        Schema::connection('tenant')->create('report_hourly_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('date');
            $table->tinyInteger('hour');
            $table->integer('orders_count')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'location_id', 'date', 'hour'], 'report_hourly_identity_unique');
        });

        Schema::connection('tenant')->create('report_kpi_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('date');
            $table->decimal('sales', 12, 2)->default(0);
            $table->integer('orders')->default(0);
            $table->decimal('avg_order', 12, 2)->default(0);
            $table->decimal('growth_percent', 5, 2)->default(0);
            $table->tinyInteger('peak_hour')->nullable();
            $table->unsignedBigInteger('top_product_id')->nullable();
            $table->decimal('upi_percent', 5, 2)->default(0);
            $table->decimal('cash_percent', 5, 2)->default(0);
            $table->decimal('card_percent', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'location_id', 'date']);
        });
    }

    private function createCentralSchema(): void
    {
        Schema::connection('mysql')->create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('uuid')->unique();
            $table->string('industry');
            $table->string('paper_size');
            $table->json('order_data');
            $table->timestamps();
        });

        Schema::connection('mysql')->create('tax_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('gst_number')->nullable();
            $table->boolean('is_gst_enabled')->default(false);
            $table->boolean('is_inclusive')->default(false);
            $table->decimal('cgst_rate', 8, 2)->default(0);
            $table->decimal('sgst_rate', 8, 2)->default(0);
            $table->decimal('igst_rate', 8, 2)->default(0);
            $table->timestamps();
        });
    }
}
