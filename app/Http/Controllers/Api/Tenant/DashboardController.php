<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use App\Services\ProductManagement\Strategies\CategoryStrategyResolver;
use Illuminate\Http\Request;
use App\Models\Tenant\Order;
use App\Services\BusinessDayService;
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
        $this->applyTodayBusinessDateFilter($todayQuery, $locationId && $locationId != 'all' ? (int) $locationId : null);

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

        $selfPosAttention = $this->selfPosAttention($query);

        return response()->json([
            'summary' => [
                'today_orders' => $todayOrders,
                'sales' => (float) $sales,
                'customers' => $customers,
                'pending_orders' => $pendingOrders,
            ],
            'recent_orders' => $recentOrders,
            'notifications' => [
                'total' => $selfPosAttention['count'],
                'unaddressed_self_pos_orders' => $selfPosAttention,
            ],
        ]);
    }

    private function applyTodayBusinessDateFilter($query, ?int $locationId = null): void
    {
        if (Schema::hasColumn('pos_orders', 'business_date')) {
            $query->whereDate('pos_orders.business_date', app(BusinessDayService::class)->currentForLocation($locationId));
            return;
        }

        $query->whereDate('created_at', now());
    }

    private function selfPosAttention($baseQuery): array
    {
        $query = (clone $baseQuery)
            ->with(['token', 'table', 'location'])
            ->where('status', 'pending_payment')
            ->where(function ($q) {
                $q->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', self::PAID_PAYMENT_STATUS);
            })
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES)
            ->where(function ($q) {
                if (Schema::hasColumn('pos_orders', 'source')) {
                    $q->orWhere('source', 'self_pos');
                }

                if (Schema::hasColumn('pos_orders', 'meta')) {
                    $q->orWhere('meta->source', 'self_pos')
                        ->orWhere('meta->self_pos->submitted', true)
                        ->orWhere('meta->self_pos->requires_biller_confirmation', true);
                }
            });

        $count = (clone $query)->count();
        $items = (clone $query)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'customer_name' => $order->customer_name ?: 'Self POS customer',
                'customer_phone' => $order->customer_phone,
                'amount' => (float) $order->total,
                'payment_method' => data_get($order->meta, 'self_pos.payment_method'),
                'token_code' => optional($order->token)->token_code,
                'table_display' => optional($order->table)->name ?: optional($order->table)->code,
                'location_name' => optional($order->location)->name,
                'submitted_at' => data_get($order->meta, 'self_pos.submitted_at'),
                'time' => optional($order->created_at)->diffForHumans(),
            ])
            ->values();

        return [
            'type' => 'unaddressed_self_pos_orders',
            'label' => 'Self POS orders need biller confirmation',
            'count' => $count,
            'items' => $items,
        ];
    }
}
