<?php

namespace Tests\Feature;

use App\Exceptions\IdempotencyException;
use App\Models\Tenant;
use App\Models\Tenant\Customer;
use App\Models\User;
use App\Services\CustomerIdentityService;
use App\Services\IdempotencyService;
use App\Services\ModuleAccessService;
use App\Services\ModuleSettingsService;
use App\Services\ModuleEntitlementService;
use App\Services\PermissionService;
use App\Services\TenantFeatureService;
use App\Services\TenantSettingsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class RegistrationModuleFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.connections.tenant', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]);
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        DB::connection('tenant')->reconnect();
        Schema::connection('tenant')->create('pos_customers', function (Blueprint $table) {
            $table->id(); $table->string('name')->nullable(); $table->string('phone')->nullable()->index();
            $table->string('email')->nullable(); $table->timestamps();
        });
        Schema::connection('tenant')->create('idempotency_requests', function (Blueprint $table) {
            $table->id(); $table->string('scope', 100); $table->char('idempotency_key_hash', 64);
            $table->char('request_hash', 64); $table->string('status', 20); $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable(); $table->string('resource_type')->nullable(); $table->unsignedBigInteger('resource_id')->nullable();
            $table->timestamp('locked_at')->nullable(); $table->timestamp('completed_at')->nullable(); $table->timestamp('expires_at'); $table->timestamps();
            $table->unique(['scope', 'idempotency_key_hash']);
        });
        Schema::connection('tenant')->create('settings', function (Blueprint $table) {
            $table->id(); $table->string('setting_key')->unique(); $table->text('value')->nullable();
            $table->string('type')->default('string'); $table->timestamps();
        });
    }

    public function test_catalog_defines_available_registration_disabled_by_default(): void
    {
        $module = config('modules.registration_management');
        $this->assertTrue($module['available']);
        $this->assertFalse($module['default_enabled']);
        $this->assertSame('registration.access', $module['access_permission']);
        foreach (config('industries.features') as $features) $this->assertFalse($features['registration_management']);
    }

    public function test_owner_is_explicit_registration_admin_but_existing_operational_roles_are_denied(): void
    {
        $permissions = app(PermissionService::class);
        $owner = (new User())->forceFill(['role' => 'owner']);
        $manager = (new User())->forceFill(['role' => 'manager']);
        $this->assertTrue($permissions->has($owner, 'tenant.modules.manage'));
        $this->assertTrue($permissions->has($owner, 'registration.access'));
        $this->assertFalse($permissions->has($manager, 'registration.access'));
        $this->assertFalse($permissions->has($manager, 'tenant.modules.manage'));
    }

    public function test_industry_does_not_gate_module_access_and_disabled_state_does(): void
    {
        $tenant = (new Tenant())->forceFill(['id' => 9, 'industry' => 'restaurant']);
        $user = (new User())->forceFill(['tenant_id' => 9, 'role' => 'owner']);
        $features = Mockery::mock(TenantFeatureService::class);
        $features->shouldReceive('has')->with($tenant, 'registration_management')->andReturn(true, false);
        $service = new ModuleAccessService(app(ModuleEntitlementService::class), $features, app(PermissionService::class));
        $this->assertTrue($service->resolve($tenant, $user, 'registration_management')->allowed);
        $this->assertFalse($service->resolve($tenant, $user, 'registration_management')->allowed);
    }

    public function test_unknown_and_globally_unavailable_modules_fail_closed_even_for_master(): void
    {
        $tenant = (new Tenant())->forceFill(['id' => 2]);
        $master = (new User())->forceFill(['role' => 'master']);
        $features = Mockery::mock(TenantFeatureService::class);
        $features->shouldNotReceive('has');
        $service = new ModuleAccessService(app(ModuleEntitlementService::class), $features, app(PermissionService::class));
        $this->assertFalse($service->resolve($tenant, $master, 'unknown')->allowed);
        Config::set('modules.registration_management.available', false);
        $this->assertFalse($service->resolve($tenant, $master, 'registration_management')->allowed);
    }

    public function test_authorized_module_setting_write_is_allow_listed_and_cache_invalidated(): void
    {
        $tenant = (new Tenant())->forceFill(['id' => 4]);
        $tenantSettings = Mockery::mock(TenantSettingsService::class);
        $tenantSettings->shouldReceive('forget')->once()->with($tenant);
        $features = Mockery::mock(TenantFeatureService::class);
        $features->shouldReceive('forget')->once()->with($tenant);
        $features->shouldReceive('has')->once()->with($tenant, 'registration_management')->andReturnTrue();
        $entitlements = Mockery::mock(ModuleEntitlementService::class);
        $entitlements->shouldReceive('isEntitled')->once()->andReturnTrue();
        $entitlements->shouldReceive('forget')->once()->with($tenant, 'registration_management');
        $service = new ModuleSettingsService($tenantSettings, $features, $entitlements);
        $result = $service->setEnabled($tenant, 'registration_management', true);
        $this->assertTrue($result['enabled']);
        $this->assertSame('1', DB::connection('tenant')->table('settings')->where('setting_key', 'features.registration_management')->value('value'));
    }

    public function test_customer_identity_preserves_phone_matching_and_creation_semantics(): void
    {
        $service = app(CustomerIdentityService::class);
        $existing = Customer::create(['name' => 'Existing', 'phone' => '9876543210', 'email' => 'old@example.test']);
        $this->assertSame('9876543210', $service->normalizePhone('98765 43210'));
        $resolved = $service->resolveOrCreate(['name' => 'Changed', 'phone' => '9876543210']);
        $this->assertSame($existing->id, $resolved->id);
        $this->assertSame('Existing', $resolved->name);
        $created = $service->resolveOrCreate(['name' => 'New', 'phone' => '(555) 100-2000']);
        $this->assertSame('5551002000', $created->phone);
        $this->assertSame(2, Customer::count());
    }

    public function test_idempotency_replays_exact_request_and_rejects_changed_payload(): void
    {
        $service = app(IdempotencyService::class);
        $first = $service->acquire('registration.create', 'request-123', ['b' => 2, 'a' => 1]);
        $this->assertTrue($first['execute']);
        $service->complete($first['record'], 201, ['id' => 77], 'registration', 77);
        $replay = $service->acquire('registration.create', 'request-123', ['a' => 1, 'b' => 2]);
        $this->assertFalse($replay['execute']);
        $this->assertSame(['id' => 77], $replay['response']);
        $this->expectException(IdempotencyException::class);
        $service->acquire('registration.create', 'request-123', ['a' => 999]);
    }

    public function test_idempotency_scopes_are_independent_and_expired_records_are_pruned(): void
    {
        $service = app(IdempotencyService::class);
        $this->assertTrue($service->acquire('registration.create', 'shared-key', [])['execute']);
        $this->assertTrue($service->acquire('registration.renew', 'shared-key', [])['execute']);
        DB::connection('tenant')->table('idempotency_requests')->update(['expires_at' => now()->subHour()]);
        $this->assertSame(2, $service->pruneExpired());
    }
}
