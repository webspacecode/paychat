<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\ReportEngineService;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;


class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reports
        {--date= : Generate one business date}
        {--period=today : today, yesterday, week, month, custom}
        {--start_date= : Custom/report start date}
        {--end_date= : Custom/report end date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        return match ($this->option('period')) {
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
                Carbon::parse($this->requiredDateOption('start_date'))->toDateString(),
                Carbon::parse($this->requiredDateOption('end_date'))->toDateString(),
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
}
