<?php

namespace App\Services\Orders;

use App\Models\Tenant\Order;
use Illuminate\Http\Request;
use App\Models\Tenant\Product;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\Payments\TaxService;
use App\Services\Orders\Strategies\StockStrategyResolver;
use App\Http\Resources\Tenant\OrderResource;
use App\Models\Tenant\Token;
use App\Events\OrderStatusUpdated;

class OrderService
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    public function listOrders(array $filters = [], int $perPage = 20)
    {
        return Order::query()
            ->with([
                'items.product:id,name',
                'token:id,order_id,token_code,status'
            ])
            ->when(isset($filters['status']), fn($q) =>
                $q->where('status', $filters['status'])
            )
            ->latest()
            ->paginate($perPage);
    }

    public function listKitchenOrders(array $filters = [], int $perPage = 20)
    {
        return Order::query()
            ->whereHas('token', function ($q) use ($filters) {
                if (isset($filters['status'])) {
                    $q->where('status', $filters['status']);
                } else {
                    // default: only active kitchen orders
                    $q->whereIn('status', ['waiting', 'preparing', 'ready']);
                }
            })
            ->with([
                'items.product:id,name',
                'token:id,order_id,token_code,status'
            ])
            ->latest()
            ->paginate($perPage);
    }
    
    public function createOrder(array $data, ?int $customerId = null): Order
    {
        return DB::transaction(function () use ($data, $customerId){
            $customerData = [];
            if($customerId){
                $customer = Customer::find($customerId);
                $customerData = [
                    'customer_id'=>$customer->id,
                    'customer_name'=>$customer->name,
                    'customer_email'=>$customer->email,
                    'customer_phone'=>$customer->phone
                ];
            }else{
                $customerData = [
                    'customer_id'=>null,
                    'customer_name'=>$data['customer']['name'] ?? null,
                    'customer_email'=>$data['customer']['email'] ?? null,
                    'customer_phone'=>$data['customer']['phone'] ?? null,
                ];
            }

            $order = Order::create(array_merge($customerData, [
                'order_no'=>'ORD-'.date('Ymd').'-'.Str::upper(Str::random(4)),
                'status'=>'draft',
                'payment_status'=>'unpaid',
            ]));

            $subtotal = 0;
            foreach($data['items'] as $item){
                $lineTotal = $item['price'] * $item['quantity'];
                $tax = $this->taxService->calculateItemTax($item['price'],$item['quantity']);
                OrderItem::create([
                    'order_id'=>$order->id,
                    'product_id'=>$item['product_id'],
                    'quantity'=>$item['quantity'],
                    'price'=>$item['price'],
                    'discount'=>$item['discount'] ?? 0,
                    'tax'=>$tax,
                    'total'=>$lineTotal
                ]);
                $subtotal += $lineTotal;
            }

            $tax = $this->taxService->calculateOrderTax($data['items']);
            $discount = $data['discount'] ?? 0;
            $total = $this->taxService->calculateTotal($subtotal,$discount,$tax);

            $order->update(['subtotal'=>$subtotal,'tax'=>$tax,'discount'=>$discount,'total'=>$total]);

            return $order->load('items');
        });
    }

    public function updateOrder(Order $order,array $data): Order
    {
        return DB::transaction(function () use ($order,$data){
            if(isset($data['items'])){
                $order->items()->delete();
                $subtotal = 0;
                foreach($data['items'] as $item){
                    $lineTotal = $item['price'] * $item['quantity'];
                    $tax = $this->taxService->calculateItemTax($item['price'],$item['quantity']);
                    OrderItem::create([
                        'order_id'=>$order->id,
                        'product_id'=>$item['product_id'],
                        'quantity'=>$item['quantity'],
                        'price'=>$item['price'],
                        'discount'=>$item['discount'] ?? 0,
                        'tax'=>$tax,
                        'total'=>$lineTotal
                    ]);
                    $subtotal += $lineTotal;
                }
                $tax = $this->taxService->calculateOrderTax($data['items']);
                $discount = $data['discount'] ?? 0;
                $total = $this->taxService->calculateTotal($subtotal,$discount,$tax);
                $order->update(['subtotal'=>$subtotal,'tax'=>$tax,'discount'=>$discount,'total'=>$total]);
            }
            return $order->load('items');
        });
    }

    public function createDraft($locationId, $customerId = null, $orderType = null, $tableId = null)
    {
        return Order::create([
            'order_no' => strtoupper('ORD-' . Str::uuid()),
            'location_id' => $locationId,
            'customer_id' => $customerId,
            'order_type' => $orderType,
            'table_id' => $tableId,
            'status' => 'draft',
            'payment_status' => 'unpaid'
        ]);
    }

    public function addItem(Order $order, $productId, $qty)
    {
        if ($order->status !== 'draft') {
            throw new Exception("Order not editable");
        }

        $product = Product::findOrFail($productId);

        $total = $product->price * $qty;

        $order->items()->create([
            'product_id' => $productId,
            'quantity' => $qty,
            'price' => $product->price,
            'total' => $total,
        ]);

        $this->recalculate($order);
    }

    public function syncItems(Order $order, Request $request): void
    {
        if ($order->status === 'completed') {
            throw new \Exception('Completed order cannot be modified');
        }
        
        DB::transaction(function () use ($order, $request) {

            // 1️⃣ Remove old items
            $order->items()->delete();

            $subtotal = 0;

            // 2️⃣ Insert new ones
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    abort(422, "Product not found with id: ".$item['product_id']);
                }

                $lineTotal = $product->price * $item['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                    'subtotal'   => $lineTotal,
                ]);

                $subtotal += $lineTotal;
            }

            // ✅ 3️⃣ CALL RECALCULATE
            $this->recalculate($order, [
                'total' => $request->total,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'discount' => $request->discount
            ]);
        });
    }

    public function moveToPendingPayment(Order $order)
    {
        if ($order->status !== 'pending_payment') {
            if ($order->status !== 'draft') {
                throw new \Exception("Invalid state");
            }

            $order->update(['status' => 'pending_payment']);
        }
    }

    private function recalculate(Order $order, array $payload = [])
    {
        
        // ✅ This is your SUBTOTAL (from item totals)
        $subtotal = $order->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // ✅ Tax (global)
        $taxRate = 18;
        $tax = round(($subtotal * $taxRate) / 100);

        // ✅ Discount
        $discount = 0;

        if (!empty($payload['discount'])) {
            $discount = min($payload['discount'], $subtotal);
        }

        // ✅ Final total
        $finalTotal = $subtotal + $tax - $discount;
        // dd([
        //     'subtotal' => $subtotal,
        //     'tax'      => $tax,
        //     'discount' => $discount,
        //     'total'    => $finalTotal,
        // ]);
        $order->update([
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'discount' => $discount,
            'total'    => $finalTotal,
        ]);
    }

    

    public function completeOrder(Order $order)
    {
        if ($order->status !== 'pending_payment') {
            throw new Exception('Invalid state');
        }

        if ($order->payment_status !== 'paid') {
            throw new \Exception('Payment not completed');
        }

        DB::transaction(function () use ($order) {

            $resolver = new StockStrategyResolver();

            foreach ($order->items as $item) {

                $strategy = $resolver->resolve($item->product);

                $strategy->deduct($item, $order->location_id);
            }

            $order->update([
                'status' => 'completed'
            ]);
        });

        return new OrderResource(
            $order->fresh()->load('items.product', 'customer', 'location', 'payments')
        );
    }

    public function updateTokenStatus(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {

            $token = $order->token;

            if (!$token) {
                throw new \Exception('Token not found for order');
            }

            // ✅ Update token status
            $token->update([
                'status' => $status
            ]);

            // Optional: sync order status if needed
            if ($status === 'ready') {
                $order->update(['status' => 'completed']);
            } elseif ($status === 'preparing') {
                $order->update(['status' => 'in_progress']);
            }

            // 🔥 IMPORTANT: reload fresh relations
            $order->load([
                'items.product:id,name',
                'token:id,order_id,token_code,status'
            ]);

            // 🔥 BROADCAST EVENT
            event(new OrderStatusUpdated($order, $token));

            return $order;
        });
    }
}
