<?php

namespace App\Services;

use App\Models\Tenant\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportEngineService
{
    private const EXCLUDED_ORDER_STATUSES = ['draft', 'cancelled', 'void', 'refunded'];
    private const PAID_PAYMENT_STATUS = 'paid';

    public function generateDailyReports($tenantId, $date)
    {
        $date = Carbon::parse($date)->toDateString();

        foreach ($this->reportLocationIds($tenantId, $date) as $locationId) {
            $this->generateSales($tenantId, $date, $locationId);
            $this->generatePayments($tenantId, $date, $locationId);
            $this->generateTopProducts($tenantId, $date, $locationId);
            $this->generateHourly($tenantId, $date, $locationId);
            $this->generateKPI($tenantId, $date, $locationId);
        }
    }

    public function generateReportsForRange($tenantId, $startDate, $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $this->generateDailyReports($tenantId, $date->toDateString());
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
        DB::table('report_payment_breakdowns')
            ->where($this->identity($tenantId, $date, $locationId))
            ->delete();

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

        foreach ($data as $row) {
            DB::table('report_payment_breakdowns')->updateOrInsert(
                array_merge($this->identity($tenantId, $date, $locationId), [
                    'payment_method' => $row->payment_method,
                ]),
                [
                    'total_amount' => $row->total,
                    'transaction_count' => $row->count,
                    'percentage' => $total > 0 ? round(($row->total / $total) * 100, 2) : 0,
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function generateTopProducts($tenantId, string $date, ?int $locationId): void
    {
        DB::table('report_top_products_daily')
            ->where($this->identity($tenantId, $date, $locationId))
            ->delete();

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

        foreach ($data as $item) {
            DB::table('report_top_products_daily')->updateOrInsert(
                array_merge($this->identity($tenantId, $date, $locationId), [
                    'product_id' => $item->id,
                ]),
                [
                    'product_name' => $item->name,
                    'quantity_sold' => $item->qty,
                    'revenue' => $item->revenue,
                    'rank' => $rank++,
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function generateHourly($tenantId, string $date, ?int $locationId): void
    {
        DB::table('report_hourly_sales')
            ->where($this->identity($tenantId, $date, $locationId))
            ->delete();

        $data = $this->ordersForDate($date, $locationId)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->get();

        foreach ($data as $row) {
            DB::table('report_hourly_sales')->updateOrInsert(
                array_merge($this->identity($tenantId, $date, $locationId), [
                    'hour' => $row->hour,
                ]),
                [
                    'orders_count' => $row->orders,
                    'revenue' => $row->revenue,
                    'updated_at' => now(),
                ]
            );
        }
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

    private function identity($tenantId, string $date, ?int $locationId): array
    {
        return [
            'tenant_id' => $tenantId,
            'location_id' => $locationId,
            'date' => $date,
        ];
    }

    private function applyReportLocationFilter($query, ?int $locationId): void
    {
        if ($locationId === null) {
            $query->whereNull('location_id');
            return;
        }

        $query->where('location_id', $locationId);
    }

    private function paymentPercentage($payments, string $paymentMethod): float
    {
        return (float) ($payments->firstWhere('payment_method', $paymentMethod)['percentage'] ?? 0);
    }
}
