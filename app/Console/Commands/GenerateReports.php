<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\ReportEngineService;
use Illuminate\Support\Facades\Config;


class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reports';

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

            app(ReportEngineService::class)
                ->generateDailyReports($tenant->id, now()->toDateString());
        }

        DB::setDefaultConnection('mysql'); // reset
    }
}
