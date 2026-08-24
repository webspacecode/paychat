<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Services\ReportEngineService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function summary(Request $request, ReportEngineService $reports)
    {
        $tenantId = $this->tenantId();
        $locationId = $this->locationId($request);
        [$start, $end] = $this->dateRange($request, $tenantId, $locationId);
        $this->logAggregationRange('summary', $tenantId, $start, $end, $locationId);
        $this->refreshAggregates($reports, $tenantId, $start, $end);

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
        $this->refreshAggregates($reports, $tenantId, $start, $end);

        return $reports->rangePayments($tenantId, $start, $end, $locationId);
    }

    public function topProducts(Request $request, ReportEngineService $reports)
    {
        $tenantId = $this->tenantId();
        $locationId = $this->locationId($request);
        [$start, $end] = $this->dateRange($request, $tenantId, $locationId);
        $this->logAggregationRange('top_products', $tenantId, $start, $end, $locationId);
        $this->refreshAggregates($reports, $tenantId, $start, $end);

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
        $this->refreshAggregates($reports, $tenantId, $start, $end);

        return $reports->rangeHourly($tenantId, $start, $end, $locationId);
    }

    public function billingByUser(Request $request, ReportEngineService $reports)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', 'in:today,month,custom'],
            'start_date' => ['required_if:period,custom', 'nullable', 'date'],
            'end_date' => ['required_if:period,custom', 'nullable', 'date', 'after_or_equal:start_date'],
            'location_id' => ['nullable'],
            'user_id' => ['nullable', 'integer'],
        ]);

        $tenantId = $this->tenantId();
        $locationId = $this->locationId($request);
        [$start, $end] = $this->billingDateRange($validated);
        $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;

        $report = $reports->billingByUser($tenantId, $start, $end, $locationId, $userId);

        return response()->json([
            'date_from' => $start->toDateString(),
            'date_to' => $end->toDateString(),
            'location_id' => $locationId,
            'user_id' => $userId,
            ...$report,
        ]);
    }

    public function dailySales(Request $request, ReportEngineService $reports)
    {
        $filters = $this->reportFilters($request);
        $this->refreshAggregates($reports, $this->tenantId(), $filters['start_date'], $filters['end_date']);

        return response()->json($this->reportPayload(
            'daily-sales',
            $filters,
            $reports->dailySalesReport($this->tenantId(), $filters['start_date'], $filters['end_date'], $filters['location_id'])
        ));
    }

    public function itemWiseSales(Request $request, ReportEngineService $reports)
    {
        $filters = $this->reportFilters($request);

        return response()->json($this->reportPayload(
            'item-wise-sales',
            $filters,
            $reports->itemWiseSalesReport($this->tenantId(), $filters['start_date'], $filters['end_date'], $filters['location_id'], $filters['limit'])
        ));
    }

    public function bestSellingProducts(Request $request, ReportEngineService $reports)
    {
        $filters = $this->reportFilters($request);
        $this->refreshAggregates($reports, $this->tenantId(), $filters['start_date'], $filters['end_date']);

        return response()->json($this->reportPayload(
            'best-selling-products',
            $filters,
            $reports->bestSellingProductsReport($this->tenantId(), $filters['start_date'], $filters['end_date'], $filters['location_id'], $filters['limit'] ?: 20)
        ));
    }

    public function cashiers(Request $request, ReportEngineService $reports)
    {
        $filters = $this->reportFilters($request);

        return response()->json($this->reportPayload(
            'cashiers',
            $filters,
            $reports->cashierReport($this->tenantId(), $filters['start'], $filters['end'], $filters['location_id'], $filters['user_id'])
        ));
    }

    public function outlets(Request $request, ReportEngineService $reports)
    {
        $filters = $this->reportFilters($request);
        $this->refreshAggregates($reports, $this->tenantId(), $filters['start_date'], $filters['end_date']);

        return response()->json($this->reportPayload(
            'outlets',
            $filters,
            $reports->outletReport($this->tenantId(), $filters['start_date'], $filters['end_date'])
        ));
    }

    public function export(Request $request, ReportEngineService $reports)
    {
        $validated = $request->validate([
            'report' => ['required', 'string', 'in:daily-sales,item-wise-sales,best-selling-products,cashiers,outlets,full-summary'],
            'format' => ['required', 'string', 'in:csv,pdf,html'],
        ]);

        $filters = $this->reportFilters($request);
        $payload = $this->exportReportPayload($validated['report'], $filters, $reports);
        $filename = $this->reportFilename($validated['report'], $filters, $validated['format'] === 'csv' ? 'csv' : 'html');

        if ($validated['format'] === 'csv') {
            return $this->csvResponse($payload, $filename);
        }

        return response($this->reportHtml($payload), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
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
            'custom_range', 'date_range' => 'custom',
            default => $period,
        };
    }

    private function reportFilters(Request $request): array
    {
        $period = $this->normalizePeriod(
            $request->get('period', $request->get('filter', 'today'))
        );

        [$startDate, $endDate] = $this->dateRange($request, $this->tenantId(), $this->locationId($request));
        $userId = $request->get('user_id', $request->get('cashier_id'));

        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start' => Carbon::parse($startDate)->startOfDay(),
            'end' => Carbon::parse($endDate)->endOfDay(),
            'location_id' => $this->locationId($request),
            'user_id' => $userId !== null && $userId !== '' ? (int) $userId : null,
            'limit' => $request->filled('limit') ? max(1, min(500, (int) $request->get('limit'))) : null,
        ];
    }

    private function billingDateRange(array $filters): array
    {
        $period = $filters['period'] ?? 'today';

        return match ($period) {
            'month' => [
                now()->startOfMonth()->startOfDay(),
                now()->endOfDay(),
            ],
            'custom' => [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ],
            default => [
                now()->startOfDay(),
                now()->endOfDay(),
            ],
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

    private function refreshAggregates(ReportEngineService $reports, $tenantId, string $startDate, string $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->diffInDays($end) > 31) {
            return;
        }

        $reports->generateReportsForRange($tenantId, $startDate, $endDate);
    }

    private function exportReportPayload(string $report, array $filters, ReportEngineService $reports): array
    {
        if (in_array($report, ['daily-sales', 'best-selling-products', 'outlets', 'full-summary'], true)) {
            $this->refreshAggregates($reports, $this->tenantId(), $filters['start_date'], $filters['end_date']);
        }

        return match ($report) {
            'daily-sales' => $this->reportPayload($report, $filters, $reports->dailySalesReport($this->tenantId(), $filters['start_date'], $filters['end_date'], $filters['location_id'])),
            'item-wise-sales' => $this->reportPayload($report, $filters, $reports->itemWiseSalesReport($this->tenantId(), $filters['start_date'], $filters['end_date'], $filters['location_id'], $filters['limit'])),
            'best-selling-products' => $this->reportPayload($report, $filters, $reports->bestSellingProductsReport($this->tenantId(), $filters['start_date'], $filters['end_date'], $filters['location_id'], $filters['limit'] ?: 20)),
            'cashiers' => $this->reportPayload($report, $filters, $reports->cashierReport($this->tenantId(), $filters['start'], $filters['end'], $filters['location_id'], $filters['user_id'])),
            'outlets' => $this->reportPayload($report, $filters, $reports->outletReport($this->tenantId(), $filters['start_date'], $filters['end_date'])),
            'full-summary' => $this->fullSummaryPayload($filters, $reports),
        };
    }

    private function fullSummaryPayload(array $filters, ReportEngineService $reports): array
    {
        $summary = $reports->rangeSummary($this->tenantId(), $filters['start_date'], $filters['end_date'], $filters['location_id']);

        return $this->reportPayload('full-summary', $filters, [
            'summary' => $summary,
            'rows' => [
                ['metric' => 'Total Sales', 'value' => $summary['total_sales']],
                ['metric' => 'Total Orders', 'value' => $summary['total_orders']],
                ['metric' => 'Average Order Value', 'value' => $summary['avg_order_value']],
                ['metric' => 'UPI Percent', 'value' => $summary['upi_percent']],
                ['metric' => 'Cash Percent', 'value' => $summary['cash_percent']],
                ['metric' => 'Card Percent', 'value' => $summary['card_percent']],
            ],
        ]);
    }

    private function reportPayload(string $report, array $filters, array $data): array
    {
        return [
            'report' => $report,
            'title' => $this->reportTitle($report),
            'date_from' => $filters['start_date'],
            'date_to' => $filters['end_date'],
            'location_id' => $filters['location_id'],
            'user_id' => $filters['user_id'],
            'generated_at' => now()->toISOString(),
            'last_refreshed_at' => now()->toISOString(),
            'summary' => $data['summary'] ?? [],
            'rows' => $data['rows'] ?? [],
        ];
    }

    private function csvResponse(array $payload, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($payload) {
            $handle = fopen('php://output', 'w');
            $rows = $payload['rows'] ?? [];

            fputcsv($handle, [$payload['title'] ?? 'PayChat Report']);
            fputcsv($handle, ['Date From', $payload['date_from'] ?? '', 'Date To', $payload['date_to'] ?? '', 'Generated At', $payload['generated_at'] ?? '']);
            fputcsv($handle, []);

            if ($rows) {
                fputcsv($handle, array_keys((array) $rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, array_values((array) $row));
                }
            } else {
                fputcsv($handle, ['No rows found']);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function reportHtml(array $payload): string
    {
        $rows = $payload['rows'] ?? [];
        $headers = $rows ? array_keys((array) $rows[0]) : [];
        $summary = $payload['summary'] ?? [];
        $title = e($payload['title'] ?? 'PayChat Report');

        $summaryHtml = collect($summary)->map(
            fn ($value, $key) => '<div class="metric"><span>'.e(str_replace('_', ' ', (string) $key)).'</span><strong>'.e((string) $value).'</strong></div>'
        )->implode('');

        $headerHtml = collect($headers)->map(fn ($header) => '<th>'.e(str_replace('_', ' ', $header)).'</th>')->implode('');
        $rowsHtml = collect($rows)->map(function ($row) use ($headers) {
            return '<tr>'.collect($headers)->map(fn ($header) => '<td>'.e((string) ($row[$header] ?? '')).'</td>')->implode('').'</tr>';
        })->implode('');

        if (! $rowsHtml) {
            $rowsHtml = '<tr><td>No rows found</td></tr>';
        }

        return <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$title}</title>
  <style>
    body { font-family: Arial, sans-serif; color: #111827; margin: 24px; }
    h1 { margin: 0 0 6px; font-size: 24px; }
    .meta { color: #6b7280; font-size: 12px; font-weight: 700; margin-bottom: 18px; }
    .summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 18px; }
    .metric { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; }
    .metric span { display: block; color: #6b7280; font-size: 11px; font-weight: 700; text-transform: capitalize; }
    .metric strong { display: block; margin-top: 5px; font-size: 16px; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th, td { border: 1px solid #e5e7eb; padding: 7px; text-align: left; }
    th { background: #f9fafb; text-transform: capitalize; }
  </style>
</head>
<body>
  <h1>{$title}</h1>
  <div class="meta">{$payload['date_from']} to {$payload['date_to']} · Generated {$payload['generated_at']}</div>
  <div class="summary">{$summaryHtml}</div>
  <table>
    <thead><tr>{$headerHtml}</tr></thead>
    <tbody>{$rowsHtml}</tbody>
  </table>
</body>
</html>
HTML;
    }

    private function reportTitle(string $report): string
    {
        return match ($report) {
            'daily-sales' => 'Daily Sales Report',
            'item-wise-sales' => 'Item-wise Sales Report',
            'best-selling-products' => 'Best-selling Products Report',
            'cashiers' => 'Cashier Report',
            'outlets' => 'Outlet Report',
            'full-summary' => 'Full Summary Report',
            default => 'PayChat Report',
        };
    }

    private function reportFilename(string $report, array $filters, string $extension): string
    {
        return sprintf(
            'paychat-%s-%s-to-%s.%s',
            preg_replace('/[^a-z0-9-]+/i', '-', $report),
            $filters['start_date'],
            $filters['end_date'],
            $extension
        );
    }
}
