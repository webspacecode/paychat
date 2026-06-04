<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\LoyaltyTransaction;
use App\Models\Tenant\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private const EXCLUDED_ORDER_STATUSES = ['draft', 'cancelled', 'void', 'refunded'];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'keyword' => ['nullable', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $validated['search'] ?? $validated['keyword'] ?? null;
        $perPage = $validated['per_page'] ?? 20;

        $customers = Customer::query()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'location_id',
                'customer_type',
                'loyalty_points',
                'created_at',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($validated['name'] ?? null, fn ($query, $name) =>
                $query->where('name', 'like', "%{$name}%")
            )
            ->when($validated['email'] ?? null, fn ($query, $email) =>
                $query->where('email', 'like', "%{$email}%")
            )
            ->when($validated['phone'] ?? null, fn ($query, $phone) =>
                $query->where('phone', 'like', "%{$phone}%")
            )
            ->latest()
            ->paginate($perPage);

        return response()->json($customers);
    }

    public function show(string $tenantSlug, Customer $customer)
    {
        return response()->json($this->customerPayload($customer));
    }

    public function summary(string $tenantSlug, Customer $customer)
    {
        $customer->loadMissing('loyaltyTransactions');

        $recentOrders = $this->completedPaidOrders($customer)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => $this->orderPayload($order))
            ->values();

        $recentTransactions = $customer->loyaltyTransactions()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (LoyaltyTransaction $transaction) => $this->transactionPayload($transaction))
            ->values();

        return response()->json([
            'customer' => $this->customerPayload($customer),
            'loyalty_points' => (int) $customer->loyalty_points,
            'total_visits' => (int) $customer->total_visits,
            'total_spend' => (float) $customer->total_spend,
            'average_order_value' => $customer->total_visits > 0
                ? round(((float) $customer->total_spend) / ((int) $customer->total_visits), 2)
                : 0,
            'last_visit_at' => optional($customer->last_visit_at)->toISOString(),
            'favourite_products' => $this->favouriteProducts($customer),
            'recent_orders' => $recentOrders,
            'recent_loyalty_transactions' => $recentTransactions,
        ]);
    }

    public function orders(string $tenantSlug, Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'include_all' => ['nullable', 'boolean'],
        ]);

        $query = $customer->orders();

        if (! ($validated['include_all'] ?? false)) {
            $query = $this->completedPaidOrders($customer);
        }

        $orders = $query
            ->latest()
            ->paginate($validated['per_page'] ?? 20);

        $orders->getCollection()->transform(fn (Order $order) => $this->orderPayload($order));

        return response()->json($orders);
    }

    public function loyaltyTransactions(string $tenantSlug, Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $transactions = $customer->loyaltyTransactions()
            ->latest()
            ->paginate($validated['per_page'] ?? 20);

        $transactions->getCollection()->transform(
            fn (LoyaltyTransaction $transaction) => $this->transactionPayload($transaction)
        );

        return response()->json($transactions);
    }

    private function completedPaidOrders(Customer $customer)
    {
        return $customer->orders()
            ->where('payment_status', 'paid')
            ->where('status', 'completed')
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES);
    }

    private function favouriteProducts(Customer $customer)
    {
        return DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->where('pos_orders.customer_id', $customer->id)
            ->where('pos_orders.payment_status', 'paid')
            ->where('pos_orders.status', 'completed')
            ->whereNotIn('pos_orders.status', self::EXCLUDED_ORDER_STATUSES)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('COALESCE(SUM(pos_order_items.quantity), 0) as quantity'),
                DB::raw('COALESCE(SUM(pos_order_items.total), 0) as spend')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity')
            ->orderByDesc('spend')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product_name,
                'quantity' => (float) $row->quantity,
                'spend' => (float) $row->spend,
            ])
            ->values();
    }

    private function customerPayload(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'location_id' => $customer->location_id,
            'customer_type' => $customer->customer_type,
            'loyalty_points' => (int) $customer->loyalty_points,
            'total_visits' => (int) $customer->total_visits,
            'total_spend' => (float) $customer->total_spend,
            'last_visit_at' => optional($customer->last_visit_at)->toISOString(),
            'meta' => $customer->meta,
            'created_at' => optional($customer->created_at)->toISOString(),
            'updated_at' => optional($customer->updated_at)->toISOString(),
        ];
    }

    private function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'invoice_no' => $order->invoice_no,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'order_type' => $order->order_type,
            'location_id' => $order->location_id,
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'tax' => (float) $order->tax,
            'total' => (float) $order->total,
            'paid_amount' => (float) $order->paid_amount,
            'business_date' => $order->business_date,
            'created_at' => optional($order->created_at)->toISOString(),
            'paid_at' => optional($order->paid_at)->toISOString(),
            'completed_at' => optional($order->completed_at)->toISOString(),
        ];
    }

    private function transactionPayload(LoyaltyTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'customer_id' => $transaction->customer_id,
            'order_id' => $transaction->order_id,
            'type' => $transaction->type,
            'points' => (int) $transaction->points,
            'amount' => $transaction->amount !== null ? (float) $transaction->amount : null,
            'balance_after' => (int) $transaction->balance_after,
            'description' => $transaction->description,
            'meta' => $transaction->meta,
            'created_by' => $transaction->created_by,
            'created_at' => optional($transaction->created_at)->toISOString(),
        ];
    }
}
