<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Tenant\ReportController;
use App\Services\ReportEngineService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportsManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['tenant', 'mysql'] as $connection) {
            Config::set("database.connections.{$connection}", [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ]);
            DB::purge($connection);
            DB::connection($connection)->reconnect();
        }

        DB::setDefaultConnection('tenant');
        $this->createCentralSchema();
        $this->createTenantSchema();

        app()->instance('currentTenant', (object) [
            'id' => 77,
            'slug' => 'reports',
            'name' => 'Reports Cafe',
            'industry' => 'cafe',
        ]);

        $this->seedReportData();
        app(ReportEngineService::class)->generateReportsForRange(77, '2026-08-01', '2026-08-02');
    }

    public function test_daily_and_item_reports_respect_location_and_paid_order_filters(): void
    {
        $reports = app(ReportEngineService::class);

        $daily = $reports->dailySalesReport(77, '2026-08-01', '2026-08-02', 1);
        $items = $reports->itemWiseSalesReport(77, '2026-08-01', '2026-08-02', 1);

        $this->assertSame(1, $daily['summary']['orders']);
        $this->assertSame(120.0, $daily['summary']['gross_sales']);
        $this->assertSame(2, $items['summary']['quantity_sold']);
        $this->assertSame('Latte', $items['rows'][0]['product_name']);
        $this->assertSame(120.0, $items['rows'][0]['net_revenue']);
    }

    public function test_cashier_report_filters_by_collected_user(): void
    {
        $report = app(ReportEngineService::class)->cashierReport(
            77,
            now()->parse('2026-08-01')->startOfDay(),
            now()->parse('2026-08-02')->endOfDay(),
            null,
            5
        );

        $this->assertSame(1, $report['summary']['total_orders']);
        $this->assertSame(120.0, $report['summary']['total_paid']);
        $this->assertSame('Asha Cashier', $report['rows'][0]['user_name']);
    }

    public function test_report_export_returns_streamed_csv_headers(): void
    {
        $request = Request::create('/reports/export', 'GET', [
            'report' => 'daily-sales',
            'format' => 'csv',
            'period' => 'custom',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
            'location_id' => 'all',
        ]);

        $response = app(ReportController::class)->export($request, app(ReportEngineService::class));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('paychat-daily-sales-2026-08-01-to-2026-08-02.csv', $response->headers->get('content-disposition'));
    }

    public function test_summary_payload_includes_peak_hour_from_hourly_aggregates(): void
    {
        $request = Request::create('/reports/summary', 'GET', [
            'period' => 'custom',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
            'location_id' => 'all',
        ]);

        $payload = app(ReportController::class)->summary($request, app(ReportEngineService::class))->getData(true);

        $this->assertSame(2, $payload['orders']);
        $this->assertEquals(200.0, $payload['sales']);
        $this->assertSame(10, $payload['peak_hour']);
        $this->assertSame('10:00 - 11:00', $payload['peak_hour_label']);
        $this->assertSame(1, $payload['peak_hour_orders']);
        $this->assertEquals(120.0, $payload['peak_hour_revenue']);
    }

    public function test_customer_report_tracks_customers_for_range_and_all_customers(): void
    {
        $reports = app(ReportEngineService::class);

        $today = $reports->customerReport(77, '2026-08-01', '2026-08-01', null, 'today');
        $all = $reports->customerReport(77, '2026-08-01', '2026-08-02', null, 'all');

        $this->assertSame(1, $today['summary']['total_customers']);
        $this->assertSame(1, $today['summary']['customers_with_sales']);
        $this->assertSame(120.0, $today['summary']['total_spend']);
        $this->assertSame(1, $today['summary']['walk_in_orders']);
        $this->assertSame('Asha Customer', $today['rows'][0]['name']);
        $this->assertSame(120.0, $today['rows'][0]['range_spend']);

        $this->assertSame(2, $all['summary']['total_customers']);
        $this->assertSame(2, $all['meta']['total']);
    }

    public function test_customer_report_respects_location_and_search_filters(): void
    {
        $reports = app(ReportEngineService::class);

        $locationOne = $reports->customerReport(77, '2026-08-01', '2026-08-02', 1, 'today');
        $search = $reports->customerReport(77, '2026-08-01', '2026-08-02', null, 'all', 'all', 'noorder');

        $this->assertSame(1, $locationOne['summary']['total_customers']);
        $this->assertSame('Asha Customer', $locationOne['rows'][0]['name']);
        $this->assertSame(1, $search['summary']['total_customers']);
        $this->assertSame('No Order Customer', $search['rows'][0]['name']);
    }

    public function test_report_generation_is_idempotent_for_all_locations(): void
    {
        $reports = app(ReportEngineService::class);

        $reports->generateDailyReports(77, '2026-08-01');
        $reports->generateDailyReports(77, '2026-08-01');
        $reports->generateDailyReports(77, '2026-08-01');

        $summary = $reports->rangeSummary(77, '2026-08-01', '2026-08-01');
        $payments = $reports->rangePayments(77, '2026-08-01', '2026-08-01')->keyBy('payment_method');
        $topProducts = $reports->rangeTopProducts(77, '2026-08-01', '2026-08-01')->keyBy('product_name');
        $hourly = $reports->rangeHourly(77, '2026-08-01', '2026-08-01')->keyBy('hour');

        $this->assertSame(2, $summary['total_orders']);
        $this->assertSame(200.0, $summary['total_sales']);
        $this->assertSame(100.0, $summary['avg_order_value']);

        $this->assertSame(120.0, $payments->get('cash')['total_amount']);
        $this->assertSame(1, $payments->get('cash')['transaction_count']);
        $this->assertSame(80.0, $payments->get('upi')['total_amount']);
        $this->assertSame(1, $payments->get('upi')['transaction_count']);

        $this->assertSame(2, $topProducts->get('Latte')['quantity_sold']);
        $this->assertSame(120.0, $topProducts->get('Latte')['revenue']);
        $this->assertSame(1, $topProducts->get('Cake')['quantity_sold']);
        $this->assertSame(80.0, $topProducts->get('Cake')['revenue']);

        $this->assertSame(1, $hourly->get(10)['orders_count']);
        $this->assertSame(120.0, $hourly->get(10)['revenue']);
        $this->assertSame(1, $hourly->get(11)['orders_count']);
        $this->assertSame(80.0, $hourly->get(11)['revenue']);

        $this->assertAggregateRowCount('report_daily_sales', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01'], 1);
        $this->assertAggregateRowCount('report_payment_breakdowns', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01', 'payment_method' => 'cash'], 1);
        $this->assertAggregateRowCount('report_top_products_daily', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01', 'product_id' => 10], 1);
        $this->assertAggregateRowCount('report_hourly_sales', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01', 'hour' => 10], 1);
        $this->assertAggregateRowCount('report_kpi_summaries', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01'], 1);
    }

    public function test_report_generation_remains_idempotent_when_unique_aggregate_indexes_are_missing(): void
    {
        DB::statement('DROP INDEX IF EXISTS report_payments_unique_identity');
        DB::statement('DROP INDEX IF EXISTS report_top_products_unique_identity');
        DB::statement('DROP INDEX IF EXISTS report_hourly_sales_unique_identity');

        $reports = app(ReportEngineService::class);

        $reports->generateDailyReports(77, '2026-08-01');
        $reports->generateDailyReports(77, '2026-08-01');
        $reports->generateDailyReports(77, '2026-08-01');

        $summary = $reports->dailySalesReport(77, '2026-08-01', '2026-08-01');

        $this->assertSame(2, $summary['summary']['orders']);
        $this->assertSame(120.0, $summary['summary']['cash_total']);
        $this->assertSame(80.0, $summary['summary']['upi_total']);
        $this->assertAggregateRowCount('report_payment_breakdowns', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01', 'payment_method' => 'cash'], 1);
        $this->assertAggregateRowCount('report_top_products_daily', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01', 'product_id' => 10], 1);
        $this->assertAggregateRowCount('report_hourly_sales', ['tenant_id' => 77, 'location_id' => 0, 'date' => '2026-08-01', 'hour' => 10], 1);
    }

    public function test_report_generation_removes_stale_payment_product_and_hour_rows(): void
    {
        DB::table('pos_payments')->where('payment_method', 'upi')->delete();
        DB::table('pos_order_items')->where('product_id', 11)->delete();
        DB::table('pos_orders')->where('id', 101)->delete();

        $reports = app(ReportEngineService::class);
        $reports->generateDailyReports(77, '2026-08-01');

        $summary = $reports->rangeSummary(77, '2026-08-01', '2026-08-01');
        $payments = $reports->rangePayments(77, '2026-08-01', '2026-08-01')->keyBy('payment_method');
        $topProducts = $reports->rangeTopProducts(77, '2026-08-01', '2026-08-01')->keyBy('product_name');
        $hourly = $reports->rangeHourly(77, '2026-08-01', '2026-08-01')->keyBy('hour');

        $this->assertSame(1, $summary['total_orders']);
        $this->assertSame(120.0, $summary['total_sales']);
        $this->assertTrue($payments->has('cash'));
        $this->assertFalse($payments->has('upi'));
        $this->assertTrue($topProducts->has('Latte'));
        $this->assertFalse($topProducts->has('Cake'));
        $this->assertTrue($hourly->has(10));
        $this->assertFalse($hourly->has(11));
    }

    private function createCentralSchema(): void
    {
        Schema::connection('mysql')->create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('role')->nullable();
            $table->timestamps();
        });
    }

    private function createTenantSchema(): void
    {
        Schema::connection('tenant')->create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->integer('total_visits')->default(0);
            $table->decimal('total_spend', 15, 2)->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->date('business_date')->nullable();
            $table->string('status')->default('draft');
            $table->string('payment_status')->default('unpaid');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('pos_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('payment_method');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->timestamps();
        });

        foreach ([
            '2026_05_10_134615_create_report_daily_sales_table.php',
            '2026_05_10_134634_create_report_payment_breakdowns_table.php',
            '2026_05_10_134652_create_report_top_products_daily_table.php',
            '2026_05_10_134711_create_report_report_hourly_sales_table.php',
            '2026_05_10_134731_create_report_kpi_summaries_table.php',
            '2026_08_25_000001_normalize_report_aggregate_location_identity.php',
        ] as $migration) {
            (include database_path("migrations/tenant/{$migration}"))->up();
        }
    }

    private function seedReportData(): void
    {
        DB::connection('mysql')->table('users')->insert([
            'id' => 5,
            'tenant_id' => 77,
            'name' => 'Asha Cashier',
            'role' => 'cashier',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('locations')->insert([
            ['id' => 1, 'name' => 'Main', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Airport', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('products')->insert([
            ['id' => 10, 'name' => 'Latte', 'sku' => 'LAT', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'Cake', 'sku' => 'CAK', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pos_customers')->insert([
            ['id' => 1, 'name' => 'Asha Customer', 'phone' => '919999000001', 'email' => 'asha@example.test', 'loyalty_points' => 24, 'total_visits' => 1, 'total_spend' => 120, 'last_visit_at' => '2026-08-01 10:05:00', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'No Order Customer', 'phone' => 'noorder', 'email' => null, 'loyalty_points' => 0, 'total_visits' => 0, 'total_spend' => 0, 'last_visit_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pos_orders')->insert([
            ['id' => 100, 'order_no' => 'R-100', 'customer_id' => 1, 'location_id' => 1, 'created_by' => 5, 'completed_by' => 5, 'business_date' => '2026-08-01', 'status' => 'completed', 'payment_status' => 'paid', 'subtotal' => 100, 'discount' => 0, 'tax' => 20, 'total' => 120, 'created_at' => '2026-08-01 10:00:00', 'updated_at' => now()],
            ['id' => 101, 'order_no' => 'R-101', 'customer_id' => null, 'location_id' => 2, 'created_by' => null, 'completed_by' => null, 'business_date' => '2026-08-01', 'status' => 'completed', 'payment_status' => 'paid', 'subtotal' => 80, 'discount' => 0, 'tax' => 0, 'total' => 80, 'created_at' => '2026-08-01 11:00:00', 'updated_at' => now()],
            ['id' => 102, 'order_no' => 'R-102', 'customer_id' => 1, 'location_id' => 1, 'created_by' => 5, 'completed_by' => 5, 'business_date' => '2026-08-01', 'status' => 'cancelled', 'payment_status' => 'paid', 'subtotal' => 500, 'discount' => 0, 'tax' => 0, 'total' => 500, 'created_at' => '2026-08-01 12:00:00', 'updated_at' => now()],
        ]);

        DB::table('pos_order_items')->insert([
            ['order_id' => 100, 'product_id' => 10, 'quantity' => 2, 'price' => 60, 'discount' => 0, 'tax' => 20, 'total' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => 101, 'product_id' => 11, 'quantity' => 1, 'price' => 80, 'discount' => 0, 'tax' => 0, 'total' => 80, 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => 102, 'product_id' => 11, 'quantity' => 10, 'price' => 50, 'discount' => 0, 'tax' => 0, 'total' => 500, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pos_payments')->insert([
            ['order_id' => 100, 'payment_method' => 'cash', 'amount' => 120, 'status' => 'success', 'collected_by' => 5, 'created_at' => '2026-08-01 10:05:00', 'updated_at' => now()],
            ['order_id' => 101, 'payment_method' => 'upi', 'amount' => 80, 'status' => 'success', 'collected_by' => null, 'created_at' => '2026-08-01 11:05:00', 'updated_at' => now()],
            ['order_id' => 102, 'payment_method' => 'cash', 'amount' => 500, 'status' => 'success', 'collected_by' => 5, 'created_at' => '2026-08-01 12:05:00', 'updated_at' => now()],
        ]);
    }

    private function assertAggregateRowCount(string $table, array $where, int $count): void
    {
        $query = DB::table($table);

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        $this->assertSame($count, $query->count());
    }
}
