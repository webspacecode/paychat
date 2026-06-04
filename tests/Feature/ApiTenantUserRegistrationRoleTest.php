<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\BootstrapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTenantUserRegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_register_defaults_tenant_user_to_owner_role(): void
    {
        $tenant = $this->tenant();

        $this->postJson('/api/register', [
            'name' => 'Tenant Owner',
            'email' => 'owner@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'tenant_id' => $tenant->id,
        ])->assertCreated()
            ->assertJsonPath('user.role', 'owner');

        $this->assertDatabaseHas('users', [
            'email' => 'owner@example.com',
            'tenant_id' => $tenant->id,
            'role' => 'owner',
        ]);
    }

    public function test_api_register_does_not_allow_master_role(): void
    {
        $tenant = $this->tenant();

        $this->postJson('/api/register', [
            'name' => 'Bad Master',
            'email' => 'bad-master@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'tenant_id' => $tenant->id,
            'role' => 'master',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'bad-master@example.com',
        ]);
    }

    public function test_bootstrap_payload_includes_tenant_api_key_for_pos_storage(): void
    {
        $tenant = $this->tenant();
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'tenant_id' => $tenant->id,
            'role' => 'owner',
        ]);

        $bootstrap = app(BootstrapService::class)->forUser($user);

        $this->assertSame('test-api-key', $bootstrap['tenant']['api_key']);
    }

    public function test_backfill_migration_assigns_owner_to_tenant_users_without_role(): void
    {
        $tenant = $this->tenant();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => null,
        ]);

        $this->artisan('migrate:refresh', [
            '--path' => 'database/migrations/2026_06_03_000001_backfill_tenant_user_roles.php',
        ]);

        $this->assertSame('owner', $user->fresh()->role);
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
