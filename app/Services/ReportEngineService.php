<?php

namespace App\Services;

use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use Illuminate\Support\Facades\DB;

class ReportEngineService
{
    public function generateDailyReports($tenantId, $date)
    {
        $this->generateSales($tenantId, $date);
        $this->generatePayments($tenantId, $date);
        $this->generateTopProducts($tenantId, $date);
        $this->generateHourly($tenantId, $date);
        $this->generateKPI($tenantId, $date);
    }

    /**
     * DAILY SALES
     */
    private function generateSales($tenantId, $date)
    {
        $orders = Order::whereDate('created_at', $date)->get();

        $totalOrders = $orders->count();
        $sales = $orders->sum('total');
        $tax = $orders->sum('tax');
        $discount = $orders->sum('discount');

        $avgOrder = $totalOrders > 0 ? $sales / $totalOrders : 0;

        DB::table('report_daily_sales')->updateOrInsert(
            [
                'tenant_id' => $tenantId,
                'date' => $date,
            ],
            [
                'total_orders' => $totalOrders,
                'total_sales' => $sales,
                'total_tax' => $tax,
                'total_discount' => $discount,
                'net_sales' => $sales - $discount,
                'avg_order_value' => $avgOrder,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * PAYMENTS BREAKDOWN
     */
    private function generatePayments($tenantId, $date)
    {
        $data = Payment::select(
                'payment_method',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('created_at', $date)
            ->groupBy('payment_method')
            ->get();

        $total = $data->sum('total');

        foreach ($data as $row) {

            DB::table('report_payment_breakdowns')->updateOrInsert(
                [
                    'tenant_id' => $tenantId,
                    'date' => $date,
                    'payment_method' => $row->payment_method,
                ],
                [
                    'total_amount' => $row->total,
                    'transaction_count' => $row->count,
                    'percentage' => $total > 0 ? round(($row->total / $total) * 100, 2) : 0,
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * TOP PRODUCTS
     */
    private function generateTopProducts($tenantId, $date)
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
            ->whereDate('pos_orders.created_at', $date)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->get();

        $rank = 1;

        foreach ($data as $item) {

            DB::table('report_top_products_daily')->updateOrInsert(
                [
                    'tenant_id' => $tenantId,
                    'date' => $date,
                    'product_id' => $item->id,
                ],
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

    /**
     * HOURLY SALES
     */
    private function generateHourly($tenantId, $date)
    {
        $data = Order::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->whereDate('created_at', $date)
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->get();

        foreach ($data as $row) {

            DB::table('report_hourly_sales')->updateOrInsert(
                [
                    'tenant_id' => $tenantId,
                    'date' => $date,
                    'hour' => $row->hour,
                ],
                [
                    'orders_count' => $row->orders,
                    'revenue' => $row->revenue,
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * KPI SUMMARY
     */
    private function generateKPI($tenantId, $date)
    {
        $sales = DB::table('report_daily_sales')
            ->where('tenant_id', $tenantId)
            ->where('date', $date)
            ->first();

        $payments = DB::table('report_payment_breakdowns')
            ->where('tenant_id', $tenantId)
            ->where('date', $date)
            ->get();

        $upi = optional($payments->where('payment_method', 'upi')->first())->percentage ?? 0;
        $cash = optional($payments->where('payment_method', 'cash')->first())->percentage ?? 0;
        $card = optional($payments->where('payment_method', 'card')->first())->percentage ?? 0;

        DB::table('report_kpi_summaries')->updateOrInsert(
            [
                'tenant_id' => $tenantId,
                'date' => $date,
            ],
            [
                'sales' => $sales->total_sales ?? 0,
                'orders' => $sales->total_orders ?? 0,
                'avg_order' => $sales->avg_order_value ?? 0,
                'upi_percent' => $upi,
                'cash_percent' => $cash,
                'card_percent' => $card,
                'updated_at' => now(),
            ]
        );
    }
}