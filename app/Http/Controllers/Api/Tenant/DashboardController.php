<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use App\Services\ProductManagement\Strategies\CategoryStrategyResolver;
use Illuminate\Http\Request;
use App\Models\Tenant\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class DashboardController extends Controller
{
    private const EXCLUDED_ORDER_STATUSES = ['draft', 'cancelled', 'void', 'refunded'];
    private const PAID_PAYMENT_STATUS = 'paid';

    protected $resolver;

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $locationId = $request->get('location_id');

        $query = Order::query();

        if ($locationId && $locationId != 'all') {
            $query->where('location_id', $locationId);
        }

        // 🔥 TODAY FILTER
        $todayQuery = (clone $query);
        $this->applyTodayBusinessDateFilter($todayQuery);

        // 🔥 SUMMARY
        $paidTodayQuery = (clone $todayQuery)
            ->where('payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES);

        $todayOrders = (clone $paidTodayQuery)->count();

        $sales = (clone $paidTodayQuery)
            ->sum(DB::raw('CAST(total AS DECIMAL(10,2))'));

        $customers = (clone $todayQuery)
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        $pendingOrders = (clone $query)
            ->where('status', 'pending')
            ->count();

        // 🔥 RECENT ORDERS
        $recentOrders = (clone $query)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' =>
                        $order->customer->name ??
                        $order->walk_in_customer['name'] ??
                        'Walk-in',

                    'items_count' => count($order->items ?? []),
                    'amount' => $order->total,
                    'status' => $order->status,

                    'time' => $order->created_at->diffForHumans()
                ];
            });

        return response()->json([
            'summary' => [
                'today_orders' => $todayOrders,
                'sales' => (float) $sales,
                'customers' => $customers,
                'pending_orders' => $pendingOrders,
            ],
            'recent_orders' => $recentOrders
        ]);
    }

    private function applyTodayBusinessDateFilter($query): void
    {
        if (Schema::hasColumn('pos_orders', 'business_date')) {
            $query->whereDate('pos_orders.business_date', now()->toDateString());
            return;
        }

        $query->whereDate('created_at', now());
    }
}
