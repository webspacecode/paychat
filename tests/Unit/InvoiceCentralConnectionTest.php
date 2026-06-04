<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

class InvoiceCentralConnectionTest extends TestCase
{
    private array $mysqlConnection;

    private array $tenantConnection;

    private string $defaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mysqlConnection = config('database.connections.mysql');
        $this->tenantConnection = config('database.connections.tenant');
        $this->defaultConnection = DB::getDefaultConnection();
    }

    protected function tearDown(): void
    {
        DB::disconnect('mysql');
        DB::disconnect('tenant');
        Config::set('database.connections.mysql', $this->mysqlConnection);
        Config::set('database.connections.tenant', $this->tenantConnection);
        DB::setDefaultConnection($this->defaultConnection);

        parent::tearDown();
    }

    public function test_invoice_model_is_bound_to_central_connection_even_when_default_is_tenant(): void
    {
        DB::setDefaultConnection('tenant');

        $this->assertSame(Invoice::CENTRAL_CONNECTION, (new Invoice())->getConnectionName());
        $this->assertSame(Invoice::CENTRAL_CONNECTION, Invoice::query()->getModel()->getConnectionName());
    }

    public function test_invoice_service_forces_invoice_queries_to_central_connection(): void
    {
        DB::setDefaultConnection('tenant');

        $service = new InvoiceService();
        $method = (new ReflectionClass($service))->getMethod('invoiceQuery');

        $query = $method->invoke($service);

        $this->assertSame(Invoice::CENTRAL_CONNECTION, $query->getModel()->getConnectionName());
    }

    public function test_invoice_number_generation_checks_central_invoices_table_not_tenant_default(): void
    {
        $centralDatabase = tempnam(sys_get_temp_dir(), 'paychat-central-');
        $tenantDatabase = tempnam(sys_get_temp_dir(), 'paychat-tenant-');

        Config::set('database.connections.mysql', [
            'driver' => 'sqlite',
            'database' => $centralDatabase,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $tenantDatabase,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('mysql');
        DB::purge('tenant');

        Schema::connection('mysql')->create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
        });

        DB::setDefaultConnection('tenant');

        $invoiceNumber = InvoiceService::generateInvoiceNumber();

        $this->assertStringStartsWith('PC'.now()->format('y').'-', $invoiceNumber);
        $this->assertFalse(Schema::connection('tenant')->hasTable('invoices'));

        DB::disconnect('mysql');
        DB::disconnect('tenant');
        @unlink($centralDatabase);
        @unlink($tenantDatabase);
    }
}
