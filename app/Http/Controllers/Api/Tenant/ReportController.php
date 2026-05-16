<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Services\ReportEngineService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function summary(Request $request, ReportEngineService $reports)
    {
        $tenantId = $this->tenantId();
        $locationId = $this->locationId($request);
        [$start, $end] = $this->dateRange($request, $tenantId, $locationId);
        $this->logAggregationRange('summary', $tenantId, $start, $end, $locationId);

        $totals = $reports->rangeSummary($tenantId, $start, $end, $locationId);

        return response()->json([
            'date_from' => $start,
            'date_to' => $end,
            'location_id' => $request->get('location_id'),
            'sales' => $totals['total_sales'],
            'orders' => $totals['total_orders'],
            'avg_order' => $totals['avg_order_value'],
            'upi_percent' => $totals['upi_percent'],
            'cash_percent' => $totals['cash_percent'],
            'card_percent' => $totals['card_percent'],
        ]);
    }

    public function payments(Request $request, ReportEngineService $reports)
    {
        $tenantId = $this->tenantId();
        $locationId = $this->locationId($request);
        [$start, $end] = $this->dateRange($request, $tenantId, $locationId);
        $this->logAggregationRange('payments', $tenantId, $start, $end, $locationId);

        return $reports->rangePayments($tenantId, $start, $end, $locationId);
    }

    public function topProducts(Request $request, ReportEngineService $reports)
    {
        $tenantId = $this->tenantId();
        $locationId = $this->locationId($request);
        [$start, $end] = $this->dateRange($request, $tenantId, $locationId);
        $this->logAggregationRange('top_products', $tenantId, $start, $end, $locationId);

        return $reports->rangeTopProducts(
            $tenantId,
            $start,
            $end,
            $locationId,
            (int) $request->get('limit', 10)
        );
    }

    public function hourly(Request $request, ReportEngineService $reports)
    {
        $tenantId = $this->tenantId();
        $locationId = $this->locationId($request);
        [$start, $end] = $this->dateRange($request, $tenantId, $locationId);
        $this->logAggregationRange('hourly', $tenantId, $start, $end, $locationId);

        return $reports->rangeHourly($tenantId, $start, $end, $locationId);
    }

    private function dateRange(Request $request, $tenantId, ?int $locationId): array
    {
        $period = $this->normalizePeriod(
            $request->get('period', $request->get('filter', 'today'))
        );

        return match ($period) {
            'yesterday' => [
                now()->subDay()->toDateString(),
                now()->subDay()->toDateString(),
            ],
            'last_7_days', 'week' => [
                now()->subDays(6)->toDateString(),
                now()->toDateString(),
            ],
            'month' => [
                now()->startOfMonth()->toDateString(),
                now()->toDateString(),
            ],
            'all' => [
                $this->firstReportDate($tenantId, $locationId),
                now()->toDateString(),
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

    private function normalizePeriod($period): string
    {
        $period = strtolower(trim((string) $period));
        $period = str_replace(['-', ' '], '_', $period);

        return match ($period) {
            '7_days', 'last_7_day', 'last_7_days' => 'last_7_days',
            'this_month', 'current_month' => 'month',
            default => $period,
        };
    }

    private function locationId(Request $request): ?int
    {
        $locationId = $request->get('location_id');
        $allLocations = in_array(strtolower(trim((string) $locationId)), ['all', '*'], true);

        if ($request->filled('location_id') && !$allLocations) {
            return (int) $locationId;
        }

        return null;
    }

    private function tenantId()
    {
        return app('currentTenant')->id;
    }

    private function firstReportDate($tenantId, ?int $locationId): string
    {
        $query = DB::table('report_daily_sales')
            ->where('tenant_id', $tenantId);

        if ($locationId === null) {
            $query->whereNull('location_id');
        } else {
            $query->where('location_id', $locationId);
        }

        $firstDate = $query->min('date');

        return $firstDate
            ? Carbon::parse($firstDate)->toDateString()
            : now()->toDateString();
    }

    private function logAggregationRange(string $report, $tenantId, string $startDate, string $endDate, ?int $locationId): void
    {
        Log::debug('Dashboard report aggregation range resolved', [
            'report' => $report,
            'tenant_id' => $tenantId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'location_filter' => $locationId === null ? 'all_locations' : "location:{$locationId}",
        ]);
    }
}
