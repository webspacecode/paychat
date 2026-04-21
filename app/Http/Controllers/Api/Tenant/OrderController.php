<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Models\Tenant\Order;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use App\Services\Orders\OrderService;
use App\Services\Payments\TaxService;
use App\Http\Requests\Tenant\CreateOrderRequest;
use App\Http\Requests\Tenant\UpdateOrderRequest;
use App\Services\Orders\Strategies\StockStrategyResolver;
use App\Http\Resources\Tenant\OrderResource;
use App\Http\Controllers\Controller;

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
        $order = $this->orderService->createDraft(
            $request->location_id,
            $request->customer_id,
            $request->order_type,
            $request->table_id
        );

        return new OrderResource(
            $order->load('items.product', 'customer', 'location', 'payments')
        );
    }

    public function updateItems(String $tenantSlug, String $orderId, Request $request, OrderService $service) 
    {
        $order = Order::findOrFail($orderId);

        $service->syncItems($order, $request);

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments')
        );
    }

    public function moveToPayment(String $tenantSlug, String $orderId, OrderService $service)
    {
        $order = Order::find($orderId);

        $service->moveToPendingPayment($order);

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments')
        );
    }

    public function complete($id, CheckoutService $service)
    {
        $order = Order::with('items.product')->findOrFail($id);

        $service->complete($order);

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments')
        );
    }

    public function attachCustomer(String $tenantSlug, Request $request, Order $order)
    {
        if ($order->status === 'completed') {
            return response()->json(['message' => 'Completed order cannot be modified'], 422);
        }

        if ($request->customer_id) {
            $customer = Customer::find($request->customer_id);
        }
        
        // 2. If phone exists → try to find existing
        if ($request->phone) {
            $customer = Customer::where('phone', $request->phone)->first();
        }

        if (!$customer) {
            $customer = Customer::create([
                'name'  => $request->name ?? null,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
            ]);
        }

        $order->update([
            'customer_id' => $customer->id,
        ]);

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments')
        );
    }

    public function completeOrder(String $tenantSlug, Order $order, OrderService $service)
    {
        return $service->completeOrder($order);
    }

    public function show(String $tenantSlug, String $orderId)
    {
        $order = Order::find($orderId);

        $order->load([
            'items.product',
            'customer',
            'location',
            'payments'
        ]);

        return new OrderResource($order);
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
            return response()->json([
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
