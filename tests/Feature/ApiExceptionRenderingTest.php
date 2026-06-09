<?php

namespace Tests\Feature;

use App\Exceptions\PaymentException;
use App\Models\Tenant;
use App\Models\Tenant\Order;
use App\Models\User;
use App\Services\OperationalLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApiExceptionRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_order_not_found_errors_are_rendered_with_a_friendly_message(): void
    {
        Route::get('/api/__test/missing-order', function () {
            throw (new ModelNotFoundException())->setModel(Order::class, [75]);
        });

        $response = $this->getJson('/api/__test/missing-order');

        $response
            ->assertNotFound()
            ->assertHeader('X-Request-ID')
            ->assertJsonPath('message', 'Order not found. Please create a new order.')
            ->assertJsonPath('code', 'ORDER_NOT_FOUND')
            ->assertJsonStructure(['message', 'support_code', 'code']);
    }

    public function test_payment_business_errors_are_rendered_as_unprocessable_json(): void
    {
        Route::post('/api/__test/payment-error', function () {
            throw new PaymentException(
                'Amount exceeds remaining payment',
                'PAYMENT_AMOUNT_EXCEEDS_REMAINING',
                422,
                [
                    'requested_amount' => 4000.0,
                    'remaining_amount' => 3500.0,
                ]
            );
        });

        $response = $this->postJson('/api/__test/payment-error');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Amount exceeds remaining payment')
            ->assertJsonPath('code', 'PAYMENT_AMOUNT_EXCEEDS_REMAINING')
            ->assertJsonPath('details.requested_amount', 4000)
            ->assertJsonPath('details.remaining_amount', 3500);
    }

    public function test_generic_500_api_exception_writes_searchable_operational_log_without_sensitive_data(): void
    {
        $requestId = 'test-500-'.Str::random(12);

        Route::get('/api/__test/rendered-500', function () {
            throw new \RuntimeException('Unexpected failure password=secret token=abc123 card_number=4111111111111111');
        });

        $response = $this->withHeader('X-Request-ID', $requestId)
            ->getJson('/api/__test/rendered-500');

        $response
            ->assertStatus(500)
            ->assertHeader('X-Request-ID', $requestId)
            ->assertJsonPath('message', 'Server error')
            ->assertJsonPath('support_code', $requestId);

        $row = $this->findOperationalRow($requestId);

        $this->assertSame('api.exception.rendered', $row['event']);
        $this->assertSame('error', $row['level']);
        $this->assertSame(500, $row['status_code']);
        $this->assertSame($requestId, $row['support_code']);
        $this->assertSame($requestId, $row['request_id']);
        $this->assertSame('GET', $row['method']);
        $this->assertSame('api/__test/rendered-500', $row['path']);
        $this->assertSame(\RuntimeException::class, $row['exception_class']);
        $this->assertStringContainsString('password=[redacted]', $row['exception_message']);
        $this->assertStringContainsString('token=[redacted]', $row['exception_message']);
        $this->assertStringContainsString('card_number=[redacted]', $row['exception_message']);
        $this->assertStringNotContainsString('secret', json_encode($row));
        $this->assertStringNotContainsString('abc123', json_encode($row));
        $this->assertStringNotContainsString('4111111111111111', json_encode($row));
        $this->assertArrayHasKey('file', $row);
        $this->assertArrayHasKey('line', $row);
    }

    public function test_404_api_error_writes_warning_level_operational_log(): void
    {
        $requestId = 'test-404-'.Str::random(12);

        Route::get('/api/__test/rendered-404', function () {
            abort(404, 'Missing token=abc123');
        });

        $response = $this->withHeader('X-Request-ID', $requestId)
            ->getJson('/api/__test/rendered-404');

        $response
            ->assertNotFound()
            ->assertJsonPath('support_code', $requestId);

        $row = $this->findOperationalRow($requestId);

        $this->assertSame('api.exception.rendered', $row['event']);
        $this->assertSame('warning', $row['level']);
        $this->assertSame(404, $row['status_code']);
        $this->assertSame($requestId, $row['support_code']);
        $this->assertStringContainsString('token=[redacted]', $row['exception_message']);
        $this->assertArrayNotHasKey('file', $row);
        $this->assertArrayNotHasKey('line', $row);
    }

    public function test_422_validation_errors_keep_response_format_and_skip_operational_log(): void
    {
        $requestId = 'test-422-'.Str::random(12);

        Route::post('/api/__test/rendered-422', function () {
            throw ValidationException::withMessages([
                'email' => ['The email field is required.'],
            ]);
        });

        $response = $this->withHeader('X-Request-ID', $requestId)
            ->postJson('/api/__test/rendered-422');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('support_code', $requestId)
            ->assertJsonValidationErrors('email');

        $this->assertNull($this->findOperationalRow($requestId, fail: false));
    }

    public function test_error_after_tenant_resolution_writes_under_correct_tenant_log(): void
    {
        $tenant = $this->tenant();
        $requestId = 'test-tenant-'.Str::random(12);

        Route::get('/api/__test/rendered-tenant', function () use ($tenant) {
            app()->instance('currentTenant', $tenant);

            throw new \RuntimeException('Tenant scoped failure');
        });

        $this->withHeader('X-Request-ID', $requestId)
            ->getJson('/api/__test/rendered-tenant')
            ->assertStatus(500)
            ->assertJsonPath('support_code', $requestId);

        $row = $this->findOperationalRow($requestId, ['tenant-'.$tenant->id]);

        $this->assertSame($tenant->id, $row['tenant_id']);
        $this->assertSame($tenant->slug, $row['tenant_slug']);
    }

    public function test_master_dashboard_can_search_system_support_code(): void
    {
        $master = User::factory()->create([
            'role' => 'master',
            'tenant_id' => null,
        ]);
        $requestId = 'test-system-search-'.Str::random(12);

        app(OperationalLogService::class)->write('error', 'api.exception.rendered', [
            'support_code' => $requestId,
            'request_id' => $requestId,
            'status_code' => 500,
            'exception_class' => \RuntimeException::class,
            'exception_message' => 'System failure',
            'method' => 'GET',
            'path' => 'api/__test/system',
        ]);

        $response = $this->actingAs($master)->getJson(route('master.logs.system', [
            'support_code' => $requestId,
            'event' => 'api.exception.rendered',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.support_code', $requestId);
    }

    public function test_existing_payment_offline_and_invoice_operational_logs_still_write(): void
    {
        $tenant = $this->tenant();
        app()->instance('currentTenant', $tenant);

        foreach ([
            'payment.create.failed' => 'payment',
            'offline.sync.failed' => 'offline',
            'invoice.generate.failed' => 'invoice',
        ] as $event => $module) {
            $requestId = 'test-'.$module.'-log-'.Str::random(12);

            app(OperationalLogService::class)->write('error', $event, [
                'support_code' => $requestId,
                'request_id' => $requestId,
                'status_code' => 500,
                'exception_class' => PaymentException::class,
                'exception_message' => $event,
            ]);

            $row = $this->findOperationalRow($requestId, ['tenant-'.$tenant->id]);

            $this->assertSame($event, $row['event']);
            $this->assertSame($module, $row['module']);
            $this->assertSame($tenant->id, $row['tenant_id']);
        }
    }

    private function findOperationalRow(string $supportCode, ?array $buckets = null, bool $fail = true): ?array
    {
        $buckets ??= ['system', 'unknown'];

        foreach ($buckets as $bucket) {
            foreach (glob(storage_path('logs/tenant-errors/'.$bucket.'/*.log')) ?: [] as $path) {
                foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                    $row = json_decode($line, true);

                    if (($row['support_code'] ?? null) === $supportCode) {
                        return $row;
                    }
                }
            }
        }

        if ($fail) {
            $this->fail('Operational log row not found for support code '.$supportCode);
        }

        return null;
    }

    private function tenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Frozen Cafe',
            'slug' => 'frozen-cafe',
            'database' => 'tenant_frozen_cafe',
            'industry' => 'restaurant',
            'api_key' => 'test-api-key',
        ], $overrides));
    }
}
