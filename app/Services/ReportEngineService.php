<?php

namespace App\Services;

use App\Models\Tenant\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportEngineService
{
    public function generateDailyReports($tenantId, $date)
    {
        $date = Carbon::parse($date)->toDateString();

        foreach ($this->reportLocationIds($date) as $locationId) {
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
            ->whereNotIn('pos_orders.status', ['draft', 'cancelled', 'void', 'refunded'])
            ->when($locationId, fn ($q) => $q->where('pos_orders.location_id', $locationId))
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
        $data = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(pos_order_items.quantity) as qty'),
                DB::raw('SUM(pos_order_items.total) as revenue')
            )
            ->whereNotIn('pos_orders.status', ['draft', 'cancelled', 'void', 'refunded'])
            ->when($locationId, fn ($q) => $q->where('pos_orders.location_id', $locationId))
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

    private function reportLocationIds(string $date): array
    {
        $locations = $this->ordersForDate($date)
            ->whereNotNull('location_id')
            ->distinct()
            ->pluck('location_id')
            ->map(fn ($locationId) => (int) $locationId)
            ->all();

        return array_merge([null], $locations);
    }

    private function ordersForDate(string $date, ?int $locationId = null)
    {
        return Order::query()
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->whereNotIn('status', ['draft', 'cancelled', 'void', 'refunded'])
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
}
