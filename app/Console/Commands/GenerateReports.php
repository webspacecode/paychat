<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\BusinessDayReportingService;
use App\Services\ReportEngineService;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


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

    private const EXCLUDED_ORDER_STATUSES = ['draft', 'cancelled', 'void', 'refunded'];
    private const PAID_PAYMENT_STATUS = 'paid';

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

            $dates = $this->datesToGenerate($tenant->id);
            $start = min($dates);
            $end = max($dates);
            $dateCount = count($dates);

            $message = "Generating reports for tenant database {$tenant->database} from {$start} to {$end} ({$dateCount} dates)";
            $this->info($message);
            Log::info($message, [
                'tenant_id' => $tenant->id,
                'tenant_database' => $tenant->database,
                'start_date' => $start,
                'end_date' => $end,
                'date_count' => $dateCount,
            ]);

            $reports = app(ReportEngineService::class);

            foreach ($dates as $date) {
                $reports->generateDailyReports($tenant->id, $date);
            }
        }

        DB::setDefaultConnection('mysql'); // reset
    }

    private function datesToGenerate($tenantId): array
    {
        if ($this->option('date')) {
            $date = Carbon::parse($this->option('date'))->toDateString();
            return [$date];
        }

        $period = strtolower(trim((string) $this->option('period')));

        if ($period === 'custom') {
            return $this->datesBetween($this->customStartDate(), $this->customEndDate());
        }

        return app(BusinessDayReportingService::class)
            ->businessDatesForPeriod($tenantId, $period);
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
}
