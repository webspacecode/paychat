<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportEngineService
{
    private const ALL_LOCATIONS_ID = 0;
    private const EXCLUDED_ORDER_STATUSES = ['draft', 'cancelled', 'void', 'refunded'];
    private const PAID_PAYMENT_STATUS = 'paid';
    private const DEADLOCK_RETRY_ATTEMPTS = 5;
    private const AGGREGATE_UNIQUE_INDEXES = [
        'report_payment_breakdowns' => 'report_payments_unique_identity',
        'report_top_products_daily' => 'report_top_products_unique_identity',
        'report_hourly_sales' => 'report_hourly_sales_unique_identity',
    ];
    private const AGGREGATE_SECTIONS = ['sales', 'payments', 'top_products', 'hourly', 'kpi'];

    public function generateDailyReports($tenantId, $date, ?array $sections = null)
    {
        $date = Carbon::parse($date)->toDateString();
        $sections = $this->normalizeAggregateSections($sections);

        foreach ($this->reportLocationIds($tenantId, $date) as $locationId) {
            DB::connection('tenant')->transaction(function () use ($tenantId, $date, $locationId, $sections) {
                if (in_array('sales', $sections, true)) {
                    $this->generateSales($tenantId, $date, $locationId);
                }

                if (in_array('payments', $sections, true)) {
                    $this->generatePayments($tenantId, $date, $locationId);
                }

                if (in_array('top_products', $sections, true)) {
                    $this->generateTopProducts($tenantId, $date, $locationId);
                }

                if (in_array('hourly', $sections, true)) {
                    $this->generateHourly($tenantId, $date, $locationId);
                }

                if (in_array('kpi', $sections, true)) {
                    $this->generateKPI($tenantId, $date, $locationId);
                }
            }, self::DEADLOCK_RETRY_ATTEMPTS);
        }
    }

    public function generateReportsForRange($tenantId, $startDate, $endDate, ?array $sections = null): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $sections = $this->normalizeAggregateSections($sections);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $this->generateDailyReports($tenantId, $date->toDateString(), $sections);
        }
    }

    public function rangeSummary($tenantId, string $startDate, string $endDate, ?int $locationId = null): array
    {
        $totals = DB::table('report_daily_sales')
            ->where('tenant_id', $tenantId)
            ->tap(fn ($q) => $this->applyReportLocationFilter($q, $locationId))
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(total_orders), 0) as total_orders,
                COALESCE(SUM(total_sales), 0) as total_sales,
                COALESCE(SUM(total_tax), 0) as total_tax,
                COALESCE(SUM(total_discount), 0) as total_discount,
                COALESCE(SUM(net_sales), 0) as net_sales
            ')
            ->first();

        $payments = $this->rangePayments($tenantId, $startDate, $endDate, $locationId);

        return [
            'total_orders' => (int) ($totals->total_orders ?? 0),
            'total_sales' => (float) ($totals->total_sales ?? 0),
            'total_tax' => (float) ($totals->total_tax ?? 0),
            'total_discount' => (float) ($totals->total_discount ?? 0),
            'net_sales' => (float) ($totals->net_sales ?? 0),
            'avg_order_value' => ($totals->total_orders ?? 0) > 0
                ? round($totals->total_sales / $totals->total_orders, 2)
                : 0,
            'upi_percent' => $this->paymentPercentage($payments, 'upi'),
            'cash_percent' => $this->paymentPercentage($payments, 'cash'),
            'card_percent' => $this->paymentPercentage($payments, 'card'),
        ];
    }

    public function rangePayments($tenantId, string $startDate, string $endDate, ?int $locationId = null)
    {
        $rows = DB::table('report_payment_breakdowns')
            ->where('tenant_id', $tenantId)
            ->tap(fn ($q) => $this->applyReportLocationFilter($q, $locationId))
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
                DB::raw('COALESCE(SUM(transaction_count), 0) as transaction_count')
            )
            ->groupBy('payment_method')
            ->get();

        $total = $rows->sum('total_amount');

        return $rows->map(fn ($row) => [
            'payment_method' => $row->payment_method,
            'total_amount' => (float) $row->total_amount,
            'transaction_count' => (int) $row->transaction_count,
            'percentage' => $total > 0 ? round(($row->total_amount / $total) * 100, 2) : 0,
        ]);
    }

    public function rangeTopProducts($tenantId, string $startDate, string $endDate, ?int $locationId = null, int $limit = 10)
    {
        $rows = DB::table('report_top_products_daily')
            ->where('tenant_id', $tenantId)
            ->tap(fn ($q) => $this->applyReportLocationFilter($q, $locationId))
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                'product_id',
                'product_name',
                DB::raw('COALESCE(SUM(quantity_sold), 0) as quantity_sold'),
                DB::raw('COALESCE(SUM(revenue), 0) as revenue')
            )
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $rows->values()->map(function ($row, $index) {
            return [
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'quantity_sold' => (int) $row->quantity_sold,
                'revenue' => (float) $row->revenue,
                'rank' => $index + 1,
            ];
        });
    }

    public function rangeHourly($tenantId, string $startDate, string $endDate, ?int $locationId = null)
    {
        return DB::table('report_hourly_sales')
            ->where('tenant_id', $tenantId)
            ->tap(fn ($q) => $this->applyReportLocationFilter($q, $locationId))
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                'hour',
                DB::raw('COALESCE(SUM(orders_count), 0) as orders_count'),
                DB::raw('COALESCE(SUM(revenue), 0) as revenue')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn ($row) => [
                'hour' => (int) $row->hour,
                'orders_count' => (int) $row->orders_count,
                'revenue' => (float) $row->revenue,
            ]);
    }

    public function billingByUser($tenantId, Carbon $start, Carbon $end, ?int $locationId = null, ?int $userId = null): array
    {
        $actorExpression = $this->billingActorExpression();

        $paymentRows = $this->billingBaseQuery($start, $end, $locationId, $userId, $actorExpression)
            ->selectRaw("{$actorExpression} as user_id")
            ->selectRaw('COUNT(DISTINCT pos_orders.id) as order_count')
            ->selectRaw('COALESCE(SUM(pos_payments.amount), 0) as total_paid')
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method = 'cash' THEN pos_payments.amount ELSE 0 END), 0) as cash_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method = 'upi' THEN pos_payments.amount ELSE 0 END), 0) as upi_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method = 'card' THEN pos_payments.amount ELSE 0 END), 0) as card_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method NOT IN ('cash', 'upi', 'card') THEN pos_payments.amount ELSE 0 END), 0) as other_total")
            ->selectRaw('MIN(pos_payments.created_at) as first_bill_at')
            ->selectRaw('MAX(pos_payments.created_at) as last_bill_at')
            ->groupByRaw($actorExpression)
            ->orderByDesc('total_paid')
            ->get();

        $userOrderTotals = $this->billingBaseQuery($start, $end, $locationId, $userId, $actorExpression)
            ->selectRaw("{$actorExpression} as user_id")
            ->selectRaw('pos_orders.id as order_id')
            ->selectRaw('MAX(pos_orders.total) as order_total')
            ->groupByRaw("{$actorExpression}, pos_orders.id");

        $grossRows = DB::query()
            ->fromSub($userOrderTotals, 'user_orders')
            ->select('user_id')
            ->selectRaw('COALESCE(SUM(order_total), 0) as gross_sales')
            ->groupBy('user_id')
            ->get()
            ->keyBy(fn ($row) => $row->user_id === null ? 'unassigned' : (string) $row->user_id);

        $summaryGross = DB::query()
            ->fromSub(
                $this->billingBaseQuery($start, $end, $locationId, $userId, $actorExpression)
                    ->selectRaw('pos_orders.id as order_id')
                    ->selectRaw('MAX(pos_orders.total) as order_total')
                    ->groupBy('pos_orders.id'),
                'orders'
            )
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(order_total), 0) as gross_sales')
            ->first();

        $userIds = $paymentRows
            ->pluck('user_id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $users = User::on('mysql')
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'role'])
            ->keyBy('id');

        $rows = $paymentRows->map(function ($row) use ($users, $grossRows) {
            $userId = $row->user_id === null ? null : (int) $row->user_id;
            $grossKey = $userId === null ? 'unassigned' : (string) $userId;
            $user = $userId ? $users->get($userId) : null;

            return [
                'user_id' => $userId,
                'user_name' => $user?->name ?: ($userId ? "User #{$userId}" : 'Unassigned / Kiosk'),
                'user_role' => $user?->role ?: ($userId ? null : 'kiosk'),
                'order_count' => (int) $row->order_count,
                'gross_sales' => round((float) ($grossRows->get($grossKey)->gross_sales ?? 0), 2),
                'total_paid' => round((float) $row->total_paid, 2),
                'cash_total' => round((float) $row->cash_total, 2),
                'upi_total' => round((float) $row->upi_total, 2),
                'card_total' => round((float) $row->card_total, 2),
                'other_total' => round((float) $row->other_total, 2),
                'first_bill_at' => $row->first_bill_at,
                'last_bill_at' => $row->last_bill_at,
            ];
        })->values();

        return [
            'summary' => [
                'total_orders' => (int) ($summaryGross->total_orders ?? 0),
                'gross_sales' => round((float) ($summaryGross->gross_sales ?? 0), 2),
                'total_paid' => round((float) $paymentRows->sum('total_paid'), 2),
                'cash_total' => round((float) $paymentRows->sum('cash_total'), 2),
                'upi_total' => round((float) $paymentRows->sum('upi_total'), 2),
                'card_total' => round((float) $paymentRows->sum('card_total'), 2),
                'other_total' => round((float) $paymentRows->sum('other_total'), 2),
            ],
            'rows' => $rows,
        ];
    }

    public function dailySalesReport($tenantId, string $startDate, string $endDate, ?int $locationId = null): array
    {
        $sales = DB::table('report_daily_sales')
            ->where('tenant_id', $tenantId)
            ->tap(fn ($q) => $this->applyReportLocationFilter($q, $locationId))
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $paymentRows = DB::table('report_payment_breakdowns')
            ->where('tenant_id', $tenantId)
            ->tap(fn ($q) => $this->applyReportLocationFilter($q, $locationId))
            ->whereBetween('date', [$startDate, $endDate])
            ->select('date', 'payment_method', DB::raw('COALESCE(SUM(total_amount), 0) as amount'))
            ->groupBy('date', 'payment_method')
            ->get()
            ->groupBy('date');

        $rows = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $day = $sales->get($key);
            $payments = $paymentRows->get($key, collect())->keyBy('payment_method');

            $rows[] = [
                'date' => $key,
                'orders' => (int) ($day->total_orders ?? 0),
                'gross_sales' => round((float) ($day->total_sales ?? 0), 2),
                'discount' => round((float) ($day->total_discount ?? 0), 2),
                'tax' => round((float) ($day->total_tax ?? 0), 2),
                'net_sales' => round((float) ($day->net_sales ?? 0), 2),
                'avg_order_value' => round((float) ($day->avg_order_value ?? 0), 2),
                'cash_total' => round((float) ($payments->get('cash')->amount ?? 0), 2),
                'upi_total' => round((float) ($payments->get('upi')->amount ?? 0), 2),
                'card_total' => round((float) ($payments->get('card')->amount ?? 0), 2),
                'other_total' => round((float) $paymentRows->get($key, collect())
                    ->whereNotIn('payment_method', ['cash', 'upi', 'card'])
                    ->sum('amount'), 2),
            ];
        }

        return [
            'summary' => $this->summarizeSalesRows($rows),
            'rows' => $rows,
        ];
    }

    public function itemWiseSalesReport($tenantId, string $startDate, string $endDate, ?int $locationId = null, ?int $limit = null): array
    {
        $rows = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->where('pos_orders.payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('pos_orders.status', self::EXCLUDED_ORDER_STATUSES)
            ->when($locationId !== null, fn ($q) => $q->where('pos_orders.location_id', $locationId))
            ->tap(fn ($q) => $this->applyOrderDateRangeFilter($q, $startDate, $endDate))
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                DB::raw('COALESCE(SUM(pos_order_items.quantity), 0) as quantity_sold'),
                DB::raw('COALESCE(SUM(pos_order_items.price * pos_order_items.quantity), 0) as gross_revenue'),
                DB::raw('COALESCE(SUM(pos_order_items.discount), 0) as discount'),
                DB::raw('COALESCE(SUM(pos_order_items.tax), 0) as tax'),
                DB::raw('COALESCE(SUM(pos_order_items.total), 0) as net_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('net_revenue')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product_name,
                'sku' => $row->sku,
                'quantity_sold' => (int) $row->quantity_sold,
                'gross_revenue' => round((float) $row->gross_revenue, 2),
                'discount' => round((float) $row->discount, 2),
                'tax' => round((float) $row->tax, 2),
                'net_revenue' => round((float) $row->net_revenue, 2),
                'avg_price' => (int) $row->quantity_sold > 0
                    ? round((float) $row->net_revenue / (int) $row->quantity_sold, 2)
                    : 0,
            ])
            ->values()
            ->all();

        return [
            'summary' => [
                'products' => count($rows),
                'quantity_sold' => array_sum(array_column($rows, 'quantity_sold')),
                'gross_revenue' => round(array_sum(array_column($rows, 'gross_revenue')), 2),
                'discount' => round(array_sum(array_column($rows, 'discount')), 2),
                'tax' => round(array_sum(array_column($rows, 'tax')), 2),
                'net_revenue' => round(array_sum(array_column($rows, 'net_revenue')), 2),
            ],
            'rows' => $rows,
        ];
    }

    public function bestSellingProductsReport($tenantId, string $startDate, string $endDate, ?int $locationId = null, int $limit = 20): array
    {
        $report = $this->itemWiseSalesReport($tenantId, $startDate, $endDate, $locationId, $limit);
        $totalRevenue = max(0.0, (float) ($report['summary']['net_revenue'] ?? 0));

        $report['rows'] = collect($report['rows'])->values()->map(function ($row, $index) use ($totalRevenue) {
            return [
                'rank' => $index + 1,
                ...$row,
                'contribution_percent' => $totalRevenue > 0
                    ? round(((float) $row['net_revenue'] / $totalRevenue) * 100, 2)
                    : 0,
            ];
        })->all();

        return $report;
    }

    public function cashierReport($tenantId, Carbon $start, Carbon $end, ?int $locationId = null, ?int $userId = null): array
    {
        return $this->billingByUser($tenantId, $start, $end, $locationId, $userId);
    }

    public function outletReport($tenantId, string $startDate, string $endDate): array
    {
        $locations = DB::table('locations')->pluck('name', 'id');

        $orderTotals = DB::table('pos_orders')
            ->where('payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES)
            ->tap(fn ($q) => $this->applyOrderDateRangeFilter($q, $startDate, $endDate))
            ->select('location_id')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(total), 0) as gross_sales')
            ->groupBy('location_id')
            ->get()
            ->keyBy('location_id');

        $paymentTotals = DB::table('pos_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_payments.order_id')
            ->where('pos_payments.status', 'success')
            ->where('pos_orders.payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('pos_orders.status', self::EXCLUDED_ORDER_STATUSES)
            ->tap(fn ($q) => $this->applyOrderDateRangeFilter($q, $startDate, $endDate))
            ->select('pos_orders.location_id')
            ->selectRaw('COALESCE(SUM(pos_payments.amount), 0) as paid_total')
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method = 'cash' THEN pos_payments.amount ELSE 0 END), 0) as cash_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method = 'upi' THEN pos_payments.amount ELSE 0 END), 0) as upi_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method = 'card' THEN pos_payments.amount ELSE 0 END), 0) as card_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN pos_payments.payment_method NOT IN ('cash', 'upi', 'card') THEN pos_payments.amount ELSE 0 END), 0) as other_total")
            ->groupBy('pos_orders.location_id')
            ->get()
            ->keyBy('location_id');

        $locationIds = collect($locations->keys())
            ->merge($orderTotals->keys())
            ->merge($paymentTotals->keys())
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values();

        $rows = $locationIds->map(function ($locationId) use ($locations, $orderTotals, $paymentTotals) {
            $orders = $orderTotals->get($locationId);
            $payments = $paymentTotals->get($locationId);
            $orderCount = (int) ($orders->orders ?? 0);
            $gross = round((float) ($orders->gross_sales ?? 0), 2);

            return [
                'location_id' => (int) $locationId,
                'location_name' => $locations[$locationId] ?? "Outlet #{$locationId}",
                'orders' => $orderCount,
                'gross_sales' => $gross,
                'paid_total' => round((float) ($payments->paid_total ?? 0), 2),
                'cash_total' => round((float) ($payments->cash_total ?? 0), 2),
                'upi_total' => round((float) ($payments->upi_total ?? 0), 2),
                'card_total' => round((float) ($payments->card_total ?? 0), 2),
                'other_total' => round((float) ($payments->other_total ?? 0), 2),
                'avg_order_value' => $orderCount > 0 ? round($gross / $orderCount, 2) : 0,
            ];
        })->sortByDesc('gross_sales')->values()->all();

        return [
            'summary' => [
                'outlets' => count($rows),
                'orders' => array_sum(array_column($rows, 'orders')),
                'gross_sales' => round(array_sum(array_column($rows, 'gross_sales')), 2),
                'paid_total' => round(array_sum(array_column($rows, 'paid_total')), 2),
                'cash_total' => round(array_sum(array_column($rows, 'cash_total')), 2),
                'upi_total' => round(array_sum(array_column($rows, 'upi_total')), 2),
                'card_total' => round(array_sum(array_column($rows, 'card_total')), 2),
                'other_total' => round(array_sum(array_column($rows, 'other_total')), 2),
            ],
            'rows' => $rows,
        ];
    }

    public function customerReport($tenantId, string $startDate, string $endDate, ?int $locationId = null, string $period = 'today', string $customerType = 'all', ?string $search = null, int $perPage = 50): array
    {
        if (! Schema::hasTable('pos_customers') || ! Schema::hasColumn('pos_orders', 'customer_id')) {
            return [
                'summary' => $this->emptyCustomerSummary($startDate, $endDate, $locationId),
                'rows' => [],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => max(1, min(200, $perPage)),
                    'total' => 0,
                    'last_page' => 1,
                ],
            ];
        }

        $period = strtolower((string) $period);
        $perPage = max(1, min(200, $perPage));
        $customerType = in_array($customerType, ['all', 'new', 'repeat', 'vip', 'inactive'], true) ? $customerType : 'all';

        $orderTotals = DB::table('pos_orders')
            ->where('payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES)
            ->whereNotNull('customer_id')
            ->when($locationId !== null, fn ($q) => $q->where('location_id', $locationId))
            ->tap(fn ($q) => $this->applyOrderDateRangeFilter($q, $startDate, $endDate))
            ->select('customer_id')
            ->selectRaw('COUNT(*) as orders_in_range')
            ->selectRaw('COALESCE(SUM(total), 0) as spend_in_range')
            ->selectRaw('MIN(created_at) as first_order_at')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->groupBy('customer_id');

        $selects = [
            'pos_customers.id',
            $this->customerColumn('name', "''").' as name',
            $this->customerColumn('phone', "''").' as phone',
            $this->customerColumn('email', "''").' as email',
            $this->customerColumn('loyalty_points', '0').' as loyalty_points',
            $this->customerColumn('total_visits', '0').' as total_visits',
            $this->customerColumn('total_spend', '0').' as lifetime_spend',
            $this->customerColumn('last_visit_at', 'NULL').' as last_visit_at',
            'COALESCE(range_orders.orders_in_range, 0) as orders_in_range',
            'COALESCE(range_orders.spend_in_range, 0) as spend_in_range',
            'range_orders.first_order_at',
            'range_orders.last_order_at',
        ];

        $base = DB::table('pos_customers')
            ->leftJoinSub($orderTotals, 'range_orders', 'range_orders.customer_id', '=', 'pos_customers.id')
            ->selectRaw(implode(', ', $selects));

        if ($period !== 'all') {
            $base->whereNotNull('range_orders.orders_in_range');
        }

        $search = trim((string) $search);
        if ($search !== '') {
            $base->where(function ($query) use ($search) {
                foreach (['name', 'phone', 'email'] as $column) {
                    if (Schema::hasColumn('pos_customers', $column)) {
                        $query->orWhere("pos_customers.{$column}", 'like', "%{$search}%");
                    }
                }
            });
        }

        match ($customerType) {
            'new' => $base->whereRaw('COALESCE(range_orders.orders_in_range, 0) > 0')
                ->whereRaw($this->customerColumn('total_visits', 'COALESCE(range_orders.orders_in_range, 0)').' <= 1'),
            'repeat' => $base->whereRaw($this->customerColumn('total_visits', 'COALESCE(range_orders.orders_in_range, 0)').' > 1'),
            'vip' => $base->whereRaw($this->customerColumn('total_spend', 'COALESCE(range_orders.spend_in_range, 0)').' >= 10000'),
            'inactive' => $base->where(function ($query) {
                $lastVisit = $this->customerColumn('last_visit_at', 'NULL');
                $query->whereRaw("{$lastVisit} IS NULL")
                    ->orWhereRaw("{$lastVisit} < ?", [now()->subDays(30)->toDateTimeString()]);
            }),
            default => null,
        };

        $summaryRow = DB::query()
            ->fromSub(clone $base, 'customers_report')
            ->selectRaw('COUNT(*) as total_customers')
            ->selectRaw('COALESCE(SUM(CASE WHEN orders_in_range > 0 THEN 1 ELSE 0 END), 0) as customers_with_sales')
            ->selectRaw('COALESCE(SUM(CASE WHEN total_visits <= 1 AND orders_in_range > 0 THEN 1 ELSE 0 END), 0) as new_customers')
            ->selectRaw('COALESCE(SUM(CASE WHEN total_visits > 1 THEN 1 ELSE 0 END), 0) as returning_customers')
            ->selectRaw('COALESCE(SUM(orders_in_range), 0) as total_visits')
            ->selectRaw('COALESCE(SUM(spend_in_range), 0) as total_spend')
            ->selectRaw('COALESCE(SUM(loyalty_points), 0) as loyalty_points')
            ->first();

        $walkInOrders = DB::table('pos_orders')
            ->where('payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES)
            ->whereNull('customer_id')
            ->when($locationId !== null, fn ($q) => $q->where('location_id', $locationId))
            ->tap(fn ($q) => $this->applyOrderDateRangeFilter($q, $startDate, $endDate))
            ->count();

        $paginator = $base
            ->orderByDesc('spend_in_range')
            ->orderByDesc('orders_in_range')
            ->orderBy('name')
            ->paginate($perPage);

        $rows = collect($paginator->items())->map(fn ($row) => [
            'customer_id' => (int) $row->id,
            'name' => $row->name ?: 'Walk-in saved customer',
            'phone' => $row->phone ?: '-',
            'email' => $row->email ?: '-',
            'orders' => (int) $row->orders_in_range,
            'range_spend' => round((float) $row->spend_in_range, 2),
            'lifetime_visits' => (int) $row->total_visits,
            'lifetime_spend' => round((float) $row->lifetime_spend, 2),
            'loyalty_points' => (int) $row->loyalty_points,
            'first_order_at' => $row->first_order_at,
            'last_order_at' => $row->last_order_at,
            'last_visit_at' => $row->last_visit_at,
        ])->values()->all();

        $totalCustomers = (int) ($summaryRow->total_customers ?? 0);
        $totalSpend = round((float) ($summaryRow->total_spend ?? 0), 2);

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
                'customers_with_sales' => (int) ($summaryRow->customers_with_sales ?? 0),
                'new_customers' => (int) ($summaryRow->new_customers ?? 0),
                'returning_customers' => (int) ($summaryRow->returning_customers ?? 0),
                'total_visits' => (int) ($summaryRow->total_visits ?? 0),
                'total_spend' => $totalSpend,
                'avg_spend_per_customer' => $totalCustomers > 0 ? round($totalSpend / $totalCustomers, 2) : 0,
                'loyalty_points' => (int) ($summaryRow->loyalty_points ?? 0),
                'walk_in_orders' => (int) $walkInOrders,
                'date_from' => $startDate,
                'date_to' => $endDate,
            ],
            'rows' => $rows,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    private function billingBaseQuery(Carbon $start, Carbon $end, ?int $locationId, ?int $userId, string $actorExpression)
    {
        return DB::table('pos_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_payments.order_id')
            ->where('pos_payments.status', 'success')
            ->where('pos_orders.payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('pos_orders.status', self::EXCLUDED_ORDER_STATUSES)
            ->when(
                Schema::hasColumn('pos_orders', 'business_date'),
                fn ($q) => $q->whereBetween('pos_orders.business_date', [$start->toDateString(), $end->toDateString()]),
                fn ($q) => $q->whereBetween('pos_payments.created_at', [$start, $end])
            )
            ->when($locationId !== null, fn ($q) => $q->where('pos_orders.location_id', $locationId))
            ->when($userId !== null, fn ($q) => $q->whereRaw("{$actorExpression} = ?", [$userId]));
    }

    private function billingActorExpression(): string
    {
        $columns = [];

        if (Schema::hasColumn('pos_payments', 'collected_by')) {
            $columns[] = 'pos_payments.collected_by';
        }

        if (Schema::hasColumn('pos_orders', 'completed_by')) {
            $columns[] = 'pos_orders.completed_by';
        }

        if (Schema::hasColumn('pos_orders', 'created_by')) {
            $columns[] = 'pos_orders.created_by';
        }

        return count($columns) > 1
            ? 'COALESCE('.implode(', ', $columns).')'
            : ($columns[0] ?? 'NULL');
    }

    private function customerColumn(string $column, string $fallback): string
    {
        return Schema::hasColumn('pos_customers', $column)
            ? "pos_customers.{$column}"
            : $fallback;
    }

    private function emptyCustomerSummary(string $startDate, string $endDate, ?int $locationId): array
    {
        return [
            'total_customers' => 0,
            'customers_with_sales' => 0,
            'new_customers' => 0,
            'returning_customers' => 0,
            'total_visits' => 0,
            'total_spend' => 0,
            'avg_spend_per_customer' => 0,
            'loyalty_points' => 0,
            'walk_in_orders' => 0,
            'date_from' => $startDate,
            'date_to' => $endDate,
            'location_id' => $locationId,
        ];
    }

    private function generateSales($tenantId, string $date, ?int $locationId): void
    {
        $orders = $this->ordersForDate($date, $locationId)->get();

        $totalOrders = $orders->count();
        $sales = $orders->sum('total');
        $tax = $orders->sum('tax');
        $discount = $orders->sum('discount');

        DB::table('report_daily_sales')->updateOrInsert(
            $this->identity($tenantId, $date, $locationId),
            [
                'total_orders' => $totalOrders,
                'total_sales' => $sales,
                'total_tax' => $tax,
                'total_discount' => $discount,
                'net_sales' => $sales - $discount,
                'avg_order_value' => $totalOrders > 0 ? $sales / $totalOrders : 0,
                'updated_at' => now(),
            ]
        );
    }

    private function generatePayments($tenantId, string $date, ?int $locationId): void
    {
        $data = DB::table('pos_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_payments.order_id')
            ->select(
                'pos_payments.payment_method',
                DB::raw('SUM(pos_payments.amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('pos_payments.status', 'success')
            ->where('pos_orders.payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('pos_orders.status', self::EXCLUDED_ORDER_STATUSES)
            ->when($locationId !== null, fn ($q) => $q->where('pos_orders.location_id', $locationId))
            ->tap(fn ($q) => $this->applyOrderDateFilter($q, $date))
            ->groupBy('pos_payments.payment_method')
            ->get();

        $total = $data->sum('total');
        $rows = [];

        foreach ($data as $row) {
            $rows[] = array_merge($this->identity($tenantId, $date, $locationId), [
                    'payment_method' => $row->payment_method,
                    'total_amount' => $row->total,
                    'transaction_count' => $row->count,
                    'percentage' => $total > 0 ? round(($row->total / $total) * 100, 2) : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
            ]);
        }

        $this->replaceAggregateRows(
            'report_payment_breakdowns',
            $this->identity($tenantId, $date, $locationId),
            $rows,
            ['tenant_id', 'location_id', 'date', 'payment_method'],
            ['total_amount', 'transaction_count', 'percentage', 'updated_at']
        );
    }

    private function generateTopProducts($tenantId, string $date, ?int $locationId): void
    {
        $data = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(pos_order_items.quantity) as qty'),
                DB::raw('SUM(pos_order_items.total) as revenue')
            )
            ->where('pos_orders.payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('pos_orders.status', self::EXCLUDED_ORDER_STATUSES)
            ->when($locationId !== null, fn ($q) => $q->where('pos_orders.location_id', $locationId))
            ->tap(fn ($q) => $this->applyOrderDateFilter($q, $date))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->get();

        $rank = 1;
        $rows = [];

        foreach ($data as $item) {
            $rows[] = array_merge($this->identity($tenantId, $date, $locationId), [
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'quantity_sold' => $item->qty,
                    'revenue' => $item->revenue,
                    'rank' => $rank++,
                    'created_at' => now(),
                    'updated_at' => now(),
            ]);
        }

        $this->replaceAggregateRows(
            'report_top_products_daily',
            $this->identity($tenantId, $date, $locationId),
            $rows,
            ['tenant_id', 'location_id', 'date', 'product_id'],
            ['product_name', 'quantity_sold', 'revenue', 'rank', 'updated_at']
        );
    }

    private function generateHourly($tenantId, string $date, ?int $locationId): void
    {
        $hourExpression = $this->hourExpression();
        $data = $this->ordersForDate($date, $locationId)
            ->select(
                DB::raw("{$hourExpression} as hour"),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy(DB::raw($hourExpression))
            ->get();

        $rows = [];

        foreach ($data as $row) {
            $rows[] = array_merge($this->identity($tenantId, $date, $locationId), [
                    'hour' => $row->hour,
                    'orders_count' => $row->orders,
                    'revenue' => $row->revenue,
                    'created_at' => now(),
                    'updated_at' => now(),
            ]);
        }

        $this->replaceAggregateRows(
            'report_hourly_sales',
            $this->identity($tenantId, $date, $locationId),
            $rows,
            ['tenant_id', 'location_id', 'date', 'hour'],
            ['orders_count', 'revenue', 'updated_at']
        );
    }

    private function generateKPI($tenantId, string $date, ?int $locationId): void
    {
        $sales = DB::table('report_daily_sales')
            ->where($this->identity($tenantId, $date, $locationId))
            ->first();

        $payments = DB::table('report_payment_breakdowns')
            ->where($this->identity($tenantId, $date, $locationId))
            ->get();

        DB::table('report_kpi_summaries')->updateOrInsert(
            $this->identity($tenantId, $date, $locationId),
            [
                'sales' => $sales->total_sales ?? 0,
                'orders' => $sales->total_orders ?? 0,
                'avg_order' => $sales->avg_order_value ?? 0,
                'upi_percent' => optional($payments->where('payment_method', 'upi')->first())->percentage ?? 0,
                'cash_percent' => optional($payments->where('payment_method', 'cash')->first())->percentage ?? 0,
                'card_percent' => optional($payments->where('payment_method', 'card')->first())->percentage ?? 0,
                'updated_at' => now(),
            ]
        );
    }

    private function reportLocationIds($tenantId, string $date): array
    {
        $locations = $this->ordersForDate($date)
            ->whereNotNull('location_id')
            ->distinct()
            ->pluck('location_id')
            ->map(fn ($locationId) => (int) $locationId)
            ->all();

        $existingReportLocations = DB::table('report_daily_sales')
            ->where('tenant_id', $tenantId)
            ->where('date', $date)
            ->whereNotNull('location_id')
            ->distinct()
            ->pluck('location_id')
            ->map(fn ($locationId) => (int) $locationId)
            ->filter(fn ($locationId) => $locationId > self::ALL_LOCATIONS_ID)
            ->all();

        return array_merge([null], array_values(array_unique(array_merge($locations, $existingReportLocations))));
    }

    private function ordersForDate(string $date, ?int $locationId = null)
    {
        return Order::query()
            ->when($locationId !== null, fn ($q) => $q->where('location_id', $locationId))
            ->where('payment_status', self::PAID_PAYMENT_STATUS)
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES)
            ->tap(fn ($q) => $this->applyOrderDateFilter($q, $date));
    }

    private function applyOrderDateFilter($query, string $date): void
    {
        if (Schema::hasColumn('pos_orders', 'business_date')) {
            $query->whereDate('pos_orders.business_date', $date);
            return;
        }

        $query->whereDate('pos_orders.created_at', $date);
    }

    private function applyOrderDateRangeFilter($query, string $startDate, string $endDate): void
    {
        if (Schema::hasColumn('pos_orders', 'business_date')) {
            $query->whereBetween('pos_orders.business_date', [$startDate, $endDate]);
            return;
        }

        $query->whereBetween('pos_orders.created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    private function summarizeSalesRows(array $rows): array
    {
        $orders = array_sum(array_column($rows, 'orders'));
        $gross = round(array_sum(array_column($rows, 'gross_sales')), 2);

        return [
            'orders' => $orders,
            'gross_sales' => $gross,
            'discount' => round(array_sum(array_column($rows, 'discount')), 2),
            'tax' => round(array_sum(array_column($rows, 'tax')), 2),
            'net_sales' => round(array_sum(array_column($rows, 'net_sales')), 2),
            'avg_order_value' => $orders > 0 ? round($gross / $orders, 2) : 0,
            'cash_total' => round(array_sum(array_column($rows, 'cash_total')), 2),
            'upi_total' => round(array_sum(array_column($rows, 'upi_total')), 2),
            'card_total' => round(array_sum(array_column($rows, 'card_total')), 2),
            'other_total' => round(array_sum(array_column($rows, 'other_total')), 2),
        ];
    }

    private function hourExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', created_at) AS INTEGER)"
            : 'HOUR(created_at)';
    }

    private function identity($tenantId, string $date, ?int $locationId): array
    {
        return [
            'tenant_id' => $tenantId,
            'location_id' => $this->reportLocationId($locationId),
            'date' => $date,
        ];
    }

    private function applyReportLocationFilter($query, ?int $locationId): void
    {
        $query->where('location_id', $this->reportLocationId($locationId));
    }

    private function normalizeAggregateSections(?array $sections): array
    {
        if ($sections === null) {
            return self::AGGREGATE_SECTIONS;
        }

        $sections = array_values(array_intersect(self::AGGREGATE_SECTIONS, $sections));

        return $sections ?: self::AGGREGATE_SECTIONS;
    }

    private function replaceAggregateRows(string $table, array $identity, array $rows, array $uniqueBy, array $updateColumns): void
    {
        if (! $this->hasAggregateUniqueIndex($table)) {
            DB::table($table)->where($identity)->delete();

            if ($rows) {
                DB::table($table)->insert($rows);
            }

            return;
        }

        if (! $rows) {
            DB::table($table)->where($identity)->delete();
            return;
        }

        $dimensionColumns = array_values(array_diff($uniqueBy, array_keys($identity)));

        if (count($dimensionColumns) === 1) {
            $dimensionColumn = $dimensionColumns[0];
            $currentValues = collect($rows)->pluck($dimensionColumn)->all();

            DB::table($table)
                ->where($identity)
                ->whereNotIn($dimensionColumn, $currentValues)
                ->delete();
        } else {
            DB::table($table)->where($identity)->delete();
        }

        usort($rows, function (array $a, array $b) use ($uniqueBy) {
            foreach ($uniqueBy as $column) {
                $comparison = $a[$column] <=> $b[$column];

                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        });

        DB::table($table)->upsert($rows, $uniqueBy, $updateColumns);
    }

    private function hasAggregateUniqueIndex(string $table): bool
    {
        $index = self::AGGREGATE_UNIQUE_INDEXES[$table] ?? null;

        if ($index === null || ! Schema::hasTable($table)) {
            return false;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return DB::selectOne("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?", [$table, $index]) !== null;
        }

        if ($driver === 'mysql') {
            $safeTable = str_replace('`', '``', $table);

            return DB::selectOne("SHOW INDEX FROM `{$safeTable}` WHERE Key_name = ?", [$index]) !== null;
        }

        return false;
    }

    private function reportLocationId(?int $locationId): int
    {
        return $locationId ?? self::ALL_LOCATIONS_ID;
    }

    private function paymentPercentage($payments, string $paymentMethod): float
    {
        return (float) ($payments->firstWhere('payment_method', $paymentMethod)['percentage'] ?? 0);
    }
}
