<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        return DB::table('report_kpi_summaries')
            ->latest('date')
            ->first();
    }

    public function payments(Request $request)
    {
        return DB::table('report_payment_breakdowns')
            ->whereDate('date', today())
            ->get();
    }

    public function topProducts(Request $request)
    {
        return DB::table('report_top_products_daily')
            ->whereDate('date', today())
            ->orderBy('rank')
            ->get();
    }

    public function hourly(Request $request)
    {
        return DB::table('report_hourly_sales')
            ->whereDate('date', today())
            ->orderBy('hour')
            ->get();
    }
}