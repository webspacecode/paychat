<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusinessDayReportingService
{
    public function __construct(private BusinessDayService $businessDays)
    {
    }

    public function resolvePeriod(
        $tenantId,
        string $period = 'today',
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $locationId = null
    ): array {
        $period = $this->normalizePeriod($period);

        if ($locationId !== null) {
            [$start, $end] = $this->rangeForLocation($tenantId, $period, $locationId, $startDate, $endDate);

            return $this->payload($period, $start, $end, $locationId, []);
        }

        $locationRanges = $this->locationRanges($tenantId, $period, $startDate, $endDate);

        if ($locationRanges) {
            $starts = array_column($locationRanges, 'start_date');
            $ends = array_column($locationRanges, 'end_date');

            return $this->payload($period, min($starts), max($ends), null, $locationRanges);
        }

        [$start, $end] = $this->rangeForLocation($tenantId, $period, null, $startDate, $endDate);

        return $this->payload($period, $start, $end, null, []);
    }

    public function businessDatesForPeriod(
        $tenantId,
        string $period = 'today',
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $resolved = $this->resolvePeriod($tenantId, $period, $startDate, $endDate);
        $dates = [];

        foreach ($resolved['location_date_ranges'] as $range) {
            foreach ($this->datesBetween($range['start_date'], $range['end_date']) as $date) {
                $dates[] = $date;
            }
        }

        if (! $dates) {
            $dates = $this->datesBetween($resolved['start_date'], $resolved['end_date']);
        }

        return array_values(array_unique($dates));
    }

    private function locationRanges($tenantId, string $period, ?string $startDate, ?string $endDate): array
    {
        if (! Schema::hasTable('locations')) {
            return [];
        }

        $locationIds = DB::table('locations')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return collect($locationIds)->map(function (int $locationId) use ($tenantId, $period, $startDate, $endDate) {
            [$start, $end] = $this->rangeForLocation($tenantId, $period, $locationId, $startDate, $endDate);

            return [
                'location_id' => $locationId,
                'start_date' => $start,
                'end_date' => $end,
            ];
        })->all();
    }

    private function rangeForLocation($tenantId, string $period, ?int $locationId, ?string $startDate, ?string $endDate): array
    {
        if ($period === 'custom') {
            return [
                Carbon::parse($startDate)->toDateString(),
                Carbon::parse($endDate)->toDateString(),
            ];
        }

        $today = $this->businessDays->currentForLocation($locationId);

        return match ($period) {
            'yesterday' => [
                Carbon::parse($today)->subDay()->toDateString(),
                Carbon::parse($today)->subDay()->toDateString(),
            ],
            'last_7_days', 'week' => [
                Carbon::parse($today)->subDays(6)->toDateString(),
                $today,
            ],
            'month' => [
                Carbon::parse($today)->startOfMonth()->toDateString(),
                $today,
            ],
            'all' => [
                $this->firstOrderDate($locationId) ?: $this->firstReportDate($tenantId, $locationId) ?: $today,
                $today,
            ],
            default => [
                $today,
                $today,
            ],
        };
    }

    private function firstOrderDate(?int $locationId): ?string
    {
        if (! Schema::hasTable('pos_orders')) {
            return null;
        }

        $query = DB::table('pos_orders')
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['draft', 'cancelled', 'void', 'refunded'])
            ->when($locationId !== null, fn ($q) => $q->where('location_id', $locationId));

        if (Schema::hasColumn('pos_orders', 'business_date')) {
            $query->whereNotNull('business_date');
            $column = 'business_date';
        } else {
            $column = 'created_at';
        }

        $firstDate = $query->min(DB::raw("DATE({$column})"));

        return $firstDate ? Carbon::parse($firstDate)->toDateString() : null;
    }

    private function firstReportDate($tenantId, ?int $locationId): ?string
    {
        if (! Schema::hasTable('report_daily_sales')) {
            return null;
        }

        $query = DB::table('report_daily_sales')
            ->where('tenant_id', $tenantId);

        $locationId === null
            ? $query->where('location_id', 0)
            : $query->where('location_id', $locationId);

        $firstDate = $query->min('date');

        return $firstDate ? Carbon::parse($firstDate)->toDateString() : null;
    }

    private function payload(string $period, string $startDate, string $endDate, ?int $locationId, array $locationRanges): array
    {
        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start' => Carbon::parse($startDate)->startOfDay(),
            'end' => Carbon::parse($endDate)->endOfDay(),
            'location_id' => $locationId,
            'location_date_ranges' => $locationRanges,
        ];
    }

    private function datesBetween(string $startDate, string $endDate): array
    {
        $dates = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        return $dates;
    }

    private function normalizePeriod(string $period): string
    {
        $period = strtolower(trim($period));
        $period = str_replace(['-', ' '], '_', $period);

        return match ($period) {
            '7_days', 'last_7_day', 'last_7_days' => 'last_7_days',
            'this_month', 'current_month' => 'month',
            'custom_range', 'date_range' => 'custom',
            default => $period,
        };
    }
}
