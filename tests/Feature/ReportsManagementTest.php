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

        Schema::connection('tenant')->create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
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

        DB::table('pos_orders')->insert([
            ['id' => 100, 'order_no' => 'R-100', 'location_id' => 1, 'created_by' => 5, 'completed_by' => 5, 'business_date' => '2026-08-01', 'status' => 'completed', 'payment_status' => 'paid', 'subtotal' => 100, 'discount' => 0, 'tax' => 20, 'total' => 120, 'created_at' => '2026-08-01 10:00:00', 'updated_at' => now()],
            ['id' => 101, 'order_no' => 'R-101', 'location_id' => 2, 'created_by' => null, 'completed_by' => null, 'business_date' => '2026-08-01', 'status' => 'completed', 'payment_status' => 'paid', 'subtotal' => 80, 'discount' => 0, 'tax' => 0, 'total' => 80, 'created_at' => '2026-08-01 11:00:00', 'updated_at' => now()],
            ['id' => 102, 'order_no' => 'R-102', 'location_id' => 1, 'created_by' => 5, 'completed_by' => 5, 'business_date' => '2026-08-01', 'status' => 'cancelled', 'payment_status' => 'paid', 'subtotal' => 500, 'discount' => 0, 'tax' => 0, 'total' => 500, 'created_at' => '2026-08-01 12:00:00', 'updated_at' => now()],
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
}
