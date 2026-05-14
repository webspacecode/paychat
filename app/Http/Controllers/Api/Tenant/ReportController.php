<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $query = DB::table('report_kpi_summaries')
            ->whereBetween('date', [$start, $end]);

        $this->applyLocationFilter($query, $request);

        $totals = $query
            ->selectRaw('SUM(sales) as sales')
            ->selectRaw('SUM(orders) as orders')
            ->selectRaw('AVG(upi_percent) as upi_percent')
            ->selectRaw('AVG(cash_percent) as cash_percent')
            ->selectRaw('AVG(card_percent) as card_percent')
            ->first();

        return response()->json([
            'date_from' => $start,
            'date_to' => $end,
            'location_id' => $request->get('location_id'),
            'sales' => (float) ($totals->sales ?? 0),
            'orders' => (int) ($totals->orders ?? 0),
            'avg_order' => ($totals->orders ?? 0) > 0
                ? round($totals->sales / $totals->orders, 2)
                : 0,
            'upi_percent' => (float) ($totals->upi_percent ?? 0),
            'cash_percent' => (float) ($totals->cash_percent ?? 0),
            'card_percent' => (float) ($totals->card_percent ?? 0),
        ]);
    }

    public function payments(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $query = DB::table('report_payment_breakdowns')
            ->select(
                'payment_method',
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(transaction_count) as transaction_count')
            )
            ->whereBetween('date', [$start, $end]);

        $this->applyLocationFilter($query, $request);

        $rows = $query->groupBy('payment_method')->get();
        $total = $rows->sum('total_amount');

        return $rows->map(fn ($row) => [
            'payment_method' => $row->payment_method,
            'total_amount' => (float) $row->total_amount,
            'transaction_count' => (int) $row->transaction_count,
            'percentage' => $total > 0 ? round(($row->total_amount / $total) * 100, 2) : 0,
        ]);
    }

    public function topProducts(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $query = DB::table('report_top_products_daily')
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity_sold) as quantity_sold'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->whereBetween('date', [$start, $end]);

        $this->applyLocationFilter($query, $request);

        return $query
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->limit((int) $request->get('limit', 10))
            ->get();
    }

    public function hourly(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $query = DB::table('report_hourly_sales')
            ->select(
                'hour',
                DB::raw('SUM(orders_count) as orders_count'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->whereBetween('date', [$start, $end]);

        $this->applyLocationFilter($query, $request);

        return $query
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    private function dateRange(Request $request): array
    {
        $period = $request->get('period', 'today');

        return match ($period) {
            'yesterday' => [
                now()->subDay()->toDateString(),
                now()->subDay()->toDateString(),
            ],
            'week' => [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ],
            'month' => [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ],
            'custom' => [
                Carbon::parse($request->validate([
                    'start_date' => ['required', 'date'],
                    'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                ])['start_date'])->toDateString(),
                Carbon::parse($request->end_date)->toDateString(),
            ],
            default => [
                now()->toDateString(),
                now()->toDateString(),
            ],
        };
    }

    private function applyLocationFilter($query, Request $request): void
    {
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
            return;
        }

        $query->whereNull('location_id');
    }
}
