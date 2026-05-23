<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Models\Tenant\Order;
use App\Models\Tenant\Customer;
use App\Events\KitchenBatchCreated;
use Illuminate\Http\Request;
use App\Services\Orders\OrderService;
use App\Services\TableSessionService;
use App\Services\KitchenBatchService;
use App\Services\Payments\TaxService;
use App\Http\Requests\Tenant\CreateOrderRequest;
use App\Http\Requests\Tenant\UpdateOrderRequest;
use App\Services\Orders\Strategies\StockStrategyResolver;
use App\Http\Resources\Tenant\OrderResource;
use App\Http\Controllers\Controller;
use App\Support\Observability;
use Illuminate\Validation\Rule;
use Throwable;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request, OrderService $service)
    {
        $orders = $this->orderService->listOrders(
            $request->all(),
            $request->get('per_page', 20)
        );

        return OrderResource::collection($orders);
    }

    public function kitchenIndex(Request $request, OrderService $service)
    {
        $orders = $this->orderService->listKitchenOrders(
            $request->all(),
            $request->get('per_page', 20)
        );

        return OrderResource::collection($orders);
    }

    public function create(Request $request, OrderService $service)
    {
        $deliverySource = $this->validateDeliverySource($request, $request->order_type);

        $order = $this->orderService->createDraft(
            $request->location_id,
            $request->customer_id,
            $request->order_type,
            $request->table_id,
            $request->dining_flow,
            $request->guest_count,
            $request->table_session_id,
            $deliverySource
        );

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product')
        );
    }

    public function updateDeliverySource(String $tenantSlug, String $orderId, Request $request, OrderService $service)
    {
        $order = Order::findOrFail($orderId);
        $validated = $this->validateDeliverySource($request, $order->order_type);

        $order = $service->updateDeliverySource($order, $validated);

        return new OrderResource(
            $order->load('items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product')
        );
    }

    private function validateDeliverySource(Request $request, ?string $orderType): array
    {
        if (! OrderService::isDeliveryOrderType($orderType)) {
            return [];
        }

        return $request->validate($this->deliverySourceRules());
    }

    private function deliverySourceRules(): array
    {
        return [
            'delivery_channel' => ['nullable', 'string', Rule::in(OrderService::DELIVERY_CHANNELS)],
            'delivery_channel_label' => ['nullable', 'string', 'max:100'],
            'external_order_reference' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function updateItems(String $tenantSlug, String $orderId, Request $request, OrderService $service) 
    {
        $order = Order::findOrFail($orderId);

        $service->syncItems($order, $request);

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'token', 'kitchenBatches.items.product')
        );
    }

    public function moveToPayment(String $tenantSlug, String $orderId, OrderService $service)
    {
        $order = Order::findOrFail($orderId);

        $service->moveToPendingPayment($order);

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'kitchenBatches.items.product')
        );
    }

    public function cancel(String $tenantSlug, Order $order, Request $request, OrderService $service)
    {
        $validated = $request->validate([
            'cancel_reason_type' => 'required|in:customer_changed_mind,wrong_order,duplicate_order,item_unavailable,long_wait_time,test_order,other',
            'cancel_reason' => 'nullable|string|max:1000',
        ]);

        try {
            return $service->cancelOrder($order, $validated);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function complete($id, CheckoutService $service)
    {
        $order = Order::with('items.product')->findOrFail($id);

        $service->complete($order);

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'kitchenBatches.items.product')
        );
    }

    public function attachCustomer(String $tenantSlug, Request $request, Order $order)
    {
        // if ($order->status === 'completed') {
        //     return response()->json(['message' => 'Completed order cannot be modified'], 422);
        // }
        $customer = null;
        
        if ($request->customer_id) {
            $customer = Customer::find($request->customer_id);
        }
        
        // 2. If phone exists → try to find existing
        if ($request->phone) {
            $customer = Customer::where('phone', $request->phone)->first();
        }

        if (!$customer && !empty($request->phone)) {
            $customer = Customer::create([
                'name'  => $request->name ?? null,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
            ]);
        
            $order->update([
                'customer_id' => $customer->id,
            ]);
        }

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'kitchenBatches.items.product')
        );
    }

    public function completeOrder(String $tenantSlug, Order $order, OrderService $service)
    {
        try {
            return $service->completeOrder($order);
        } catch (Throwable $e) {
            Observability::logFailure('order.complete.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'order_id' => $order->id,
                'location_id' => $order->location_id,
                'action' => 'order.complete',
            ]);

            throw $e;
        }
    }

    public function show(String $tenantSlug, String $orderId)
    {
        $order = Order::findOrFail($orderId);

        $order->load([
            'items.product',
            'customer',
            'location',
            'payments',
            'table',
            'tableSession',
            'kitchenBatches.items.product'
        ]);

        return new OrderResource($order);
    }

    public function kitchenBatches(String $tenantSlug, String $orderId)
    {
        $order = Order::with([
            'kitchenBatches.items.product',
        ])->findOrFail($orderId);
        $operationMode = app(KitchenBatchService::class)->operationMode();

        return response()->json([
            'order_id' => $order->id,
            'data' => $order->kitchenBatches->map(function ($batch) use ($operationMode) {
                return [
                    'id' => $batch->id,
                    'location_id' => $batch->location_id,
                    'order_id' => $batch->order_id,
                    'table_session_id' => $batch->table_session_id,
                    'table_id' => $batch->table_id,
                    'batch_number' => $batch->batch_number,
                    'batch_code' => $batch->batch_code,
                    'business_date' => $batch->business_date,
                    'kitchen_operation_mode' => $operationMode,
                    'status' => $batch->status,
                    'sent_at' => $batch->sent_at,
                    'created_at' => $batch->created_at,
                    'updated_at' => $batch->updated_at,
                    'items' => $batch->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => optional($item->product)->name,
                            'sku' => optional($item->product)->sku,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->total,
                            'kitchen_status' => $item->kitchen_status,
                            'kitchen_batch_id' => $item->kitchen_batch_id,
                            'sent_to_kitchen_at' => $item->sent_to_kitchen_at,
                            'item_status' => $item->item_status,
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }

    public function updateStatus(String $tenantSlug, String $order, Request $request, OrderService $service)
    {
        $request->validate([
            'status' => 'required|in:waiting,pending,preparing,ready'
        ]);

        // 🔥 Find order safely (NO findOrFail)
        $orderModel = Order::find($order);

        if (!$orderModel) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }
        
        // 🔥 Check token exists
        if (!$orderModel->token) {
            return response()->json([
                'message' => 'Token not found for this order'
            ], 404);
        }

        try {
            // 🔥 Call service
            $updatedOrder = $service->updateTokenStatus($orderModel, $request->status);

            return response()->json([
                'message' => 'Status updated successfully',
                'order' => $updatedOrder
            ]);

        } catch (\Exception $e) {
            Observability::logFailure('order.status.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'order_id' => $orderModel->id,
                'location_id' => $orderModel->location_id,
                'action' => 'order.status.update',
            ], $request);

            return response()->json([
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
                'support_code' => Observability::requestId($request),
            ], 500);
        }
    }

    public function assignTable(String $tenantSlug, Order $order, Request $request, TableSessionService $service)
    {
        $validated = $request->validate([
            'table_id' => 'required|integer|exists:resources,id',
            'guest_count' => 'nullable|integer|min:1',
            'dining_flow' => 'nullable|in:table_service',
        ]);

        $service->assignOrder(
            $order,
            (int) $validated['table_id'],
            $validated['guest_count'] ?? null
        );

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments', 'table', 'tableSession', 'kitchenBatches.items.product')
        );
    }

    public function sendToKitchen(String $tenantSlug, Order $order, KitchenBatchService $service)
    {
        try {
            $batch = $service->sendFreshItems($order);

            if ($service->shouldBroadcastToKds($batch)) {
                event(new KitchenBatchCreated($batch));
            }
        } catch (Throwable $e) {
            Observability::logFailure('kitchen.send_to_kitchen.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'order_id' => $order->id,
                'location_id' => $order->location_id,
                'action' => 'kitchen.send_to_kitchen',
            ]);

            throw $e;
        }

        return response()->json([
            'message' => 'Kitchen batch created',
            'batch' => $batch,
        ], 201);
    }
}
