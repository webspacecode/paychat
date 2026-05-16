<?php

namespace App\Services\Orders;

use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Tenant\Product;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\Payments\TaxService;
use App\Services\Inventory\StockAvailabilityService;
use App\Services\Orders\Strategies\StockStrategyResolver;
use App\Http\Resources\Tenant\OrderResource;
use App\Models\Tenant\Token;
use App\Events\OrderStatusUpdated;

class OrderService
{
    private const ACTIVE_KITCHEN_STATUSES = ['waiting', 'preparing', 'ready'];

    protected TaxService $taxService;
    protected StockAvailabilityService $stockAvailabilityService;

    public function __construct(TaxService $taxService, StockAvailabilityService $stockAvailabilityService)
    {
        $this->taxService = $taxService;
        $this->stockAvailabilityService = $stockAvailabilityService;
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
        $perPage = max(1, min($perPage, 100));
        $businessDate = $this->resolveKitchenBusinessDate($filters);
        $orderBusinessDateColumn = $this->getOrderBusinessDateColumn();
        $tokenStatuses = $filters['status'] ?? self::ACTIVE_KITCHEN_STATUSES;

        return Order::query()
            ->whereHas('token', function ($q) use ($tokenStatuses, $businessDate, $orderBusinessDateColumn) {
                is_array($tokenStatuses)
                    ? $q->whereIn('status', $tokenStatuses)
                    : $q->where('status', $tokenStatuses);

                if (!$orderBusinessDateColumn && $businessDate) {
                    $q->whereDate('token_date', $businessDate);
                }
            })
            ->when($orderBusinessDateColumn && $businessDate, fn ($q) =>
                $q->whereDate($orderBusinessDateColumn, $businessDate)
            )
            ->when($filters['order_type'] ?? null, fn ($q, $orderType) =>
                $q->where('order_type', $orderType)
            )
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_no', 'like', "%{$search}%")
                        ->orWhereHas('token', fn ($q) =>
                            $q->where('token_code', 'like', "%{$search}%")
                        );
                });
            })
            ->with([
                'items.product:id,name',
                'token:id,order_id,token_code,status,token_date'
            ])
            ->oldest('created_at')
            ->paginate($perPage);
    }

    private function resolveKitchenBusinessDate(array $filters): string
    {
        $date = $filters['business_date']
            ?? $filters['shift_date']
            ?? Setting::get('current_business_date')
            ?? Setting::get('business_date')
            ?? Setting::get('shift_date');

        if ($date) {
            return Carbon::parse($date)->toDateString();
        }

        $businessDayStart = Setting::get('business_day_start_time')
            ?? Setting::get('day_start_time');

        if ($businessDayStart) {
            $now = now();
            $start = Carbon::parse($now->toDateString().' '.$businessDayStart);

            return $now->lt($start)
                ? $now->copy()->subDay()->toDateString()
                : $now->toDateString();
        }

        return today()->toDateString();
    }

    private function getOrderBusinessDateColumn(): ?string
    {
        if (Schema::hasColumn('pos_orders', 'business_date')) {
            return 'business_date';
        }

        if (Schema::hasColumn('pos_orders', 'shift_date')) {
            return 'shift_date';
        }

        return null;
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

            $orderData = array_merge($customerData, [
                'order_no'=>'ORD-'.date('Ymd').'-'.Str::upper(Str::random(4)),
                'status'=>'draft',
                'payment_status'=>'unpaid',
            ]);

            if ($this->getOrderBusinessDateColumn()) {
                $orderData['business_date'] = $this->resolveKitchenBusinessDate([]);
            }

            $order = Order::create($orderData);

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
        $orderData = [
            'order_no' => strtoupper('ORD-' . Str::uuid()),
            'location_id' => $locationId,
            'customer_id' => $customerId,
            'order_type' => $orderType,
            'table_id' => $tableId,
            'status' => 'draft',
            'payment_status' => 'unpaid'
        ];

        if ($this->getOrderBusinessDateColumn()) {
            $orderData['business_date'] = $this->resolveKitchenBusinessDate([]);
        }

        return Order::create($orderData);
    }

    public function addItem(Order $order, $productId, $qty)
    {
        if ($order->status !== 'draft') {
            throw new \Exception("Order not editable");
        }

        $product = Product::findOrFail($productId);

        $this->stockAvailabilityService->checkProductStock(
            (int) $productId,
            (float) $qty,
            (int) $order->location_id
        );

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

        foreach ($request->items as $item) {
            if (!Product::whereKey($item['product_id'])->exists()) {
                abort(422, "Product not found with id: ".$item['product_id']);
            }
        }

        $this->stockAvailabilityService->checkProductsStock(
            (array) $request->items,
            (int) $order->location_id
        );
        
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
        $tenant = app('currentTenant');

        // ✅ Subtotal
        $subtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);

        // ✅ Get tax config using tenant
        $taxConfig = \App\Models\TaxConfig::where('tenant_id', $tenant->id)->first();

        $tax = 0;

        if ($taxConfig && $taxConfig->is_gst_enabled) {
            $taxRate = $taxConfig->cgst_rate + $taxConfig->sgst_rate;

            if ($taxConfig->is_inclusive) {
                $tax = round($subtotal - ($subtotal / (1 + ($taxRate / 100))));
            } else {
                $tax = round(($subtotal * $taxRate) / 100);
            }
        }

        // ✅ Discount
        $discount = !empty($payload['discount'])
            ? min($payload['discount'], $subtotal)
            : 0;

        // ✅ Final total
        $finalTotal = $subtotal + $tax - $discount;

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
            throw new \Exception('Invalid state');
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
