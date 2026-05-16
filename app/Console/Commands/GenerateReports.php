<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\ReportEngineService;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;


class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reports
        {--date= : Generate one business date}
        {--period=today : today, yesterday, last_7_days, week, month, custom, all}
        {--start_date= : Custom/report start date}
        {--end_date= : Custom/report end date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tenant daily reporting rows';

    private const EXCLUDED_ORDER_STATUSES = ['draft', 'cancelled', 'void', 'refunded', 'unpaid', 'pending_payment'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = DB::connection('mysql')->table('tenants')->get();

        foreach ($tenants as $tenant) {

            // 🔥 switch DB dynamically
            $base = config('database.connections.mysql');
            Config::set('database.connections.tenant', array_merge($base, [
                'database' => $tenant->database,
            ]));

            DB::purge('tenant');
            DB::setDefaultConnection('tenant');
            DB::reconnect('tenant');

            [$start, $end] = $this->dateRange();
            $dateCount = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;

            $message = "Generating reports for tenant database {$tenant->database} from {$start} to {$end} ({$dateCount} dates)";
            $this->info($message);
            Log::info($message, [
                'tenant_id' => $tenant->id,
                'tenant_database' => $tenant->database,
                'start_date' => $start,
                'end_date' => $end,
                'date_count' => $dateCount,
            ]);

            app(ReportEngineService::class)
                ->generateReportsForRange($tenant->id, $start, $end);
        }

        DB::setDefaultConnection('mysql'); // reset
    }

    private function dateRange(): array
    {
        if ($this->option('date')) {
            $date = Carbon::parse($this->option('date'))->toDateString();
            return [$date, $date];
        }

        $period = strtolower(trim((string) $this->option('period')));

        return match ($period) {
            'yesterday' => [
                now()->subDay()->toDateString(),
                now()->subDay()->toDateString(),
            ],
            'last_7_days' => [
                now()->subDays(6)->toDateString(),
                now()->toDateString(),
            ],
            'week' => [
                now()->startOfWeek()->toDateString(),
                now()->toDateString(),
            ],
            'month' => [
                now()->startOfMonth()->toDateString(),
                now()->toDateString(),
            ],
            'custom' => [
                $this->customStartDate(),
                $this->customEndDate(),
            ],
            'all' => [
                $this->firstOrderDate(),
                now()->toDateString(),
            ],
            default => [
                now()->toDateString(),
                now()->toDateString(),
            ],
        };
    }

    private function requiredDateOption(string $option): string
    {
        if (!$this->option($option)) {
            throw new \InvalidArgumentException("--{$option} is required for custom report generation.");
        }

        return $this->option($option);
    }

    private function customStartDate(): string
    {
        return Carbon::parse($this->requiredDateOption('start_date'))->toDateString();
    }

    private function customEndDate(): string
    {
        $start = Carbon::parse($this->requiredDateOption('start_date'));
        $end = Carbon::parse($this->requiredDateOption('end_date'));

        if ($end->lt($start)) {
            throw new \InvalidArgumentException('--end_date must be after or equal to --start_date.');
        }

        return $end->toDateString();
    }

    private function firstOrderDate(): string
    {
        $dateColumn = Schema::hasColumn('pos_orders', 'business_date')
            ? 'business_date'
            : 'created_at';

        $query = DB::table('pos_orders')
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES);

        if ($dateColumn === 'business_date') {
            $query->whereNotNull('business_date');
        }

        $firstDate = $query->min(DB::raw("DATE({$dateColumn})"));

        return $firstDate
            ? Carbon::parse($firstDate)->toDateString()
            : now()->toDateString();
    }
}
