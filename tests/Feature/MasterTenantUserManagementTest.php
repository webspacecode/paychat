<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterTenantUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_create_tenant_user_with_allowed_role(): void
    {
        $master = User::factory()->create([
            'role' => 'master',
            'tenant_id' => null,
        ]);
        $tenant = $this->tenant();

        $response = $this->actingAs($master)->post(route('master.tenants.users.store', $tenant), [
            'name' => 'Cafe Manager',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('tenant_user_tenant_id', $tenant->id);

        $user = User::where('email', 'manager@example.com')->firstOrFail();

        $this->assertSame($tenant->id, $user->tenant_id);
        $this->assertSame('manager', $user->role);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_tenant_id_is_taken_from_route_not_request_body(): void
    {
        $master = User::factory()->create([
            'role' => 'master',
            'tenant_id' => null,
        ]);
        $selectedTenant = $this->tenant(['slug' => 'selected-cafe', 'database' => 'tenant_selected_cafe']);
        $otherTenant = $this->tenant([
            'slug' => 'other-cafe',
            'database' => 'tenant_other_cafe',
            'api_key' => 'other-api-key',
        ]);

        $this->actingAs($master)->post(route('master.tenants.users.store', $selectedTenant), [
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'tenant_id' => $otherTenant->id,
            'role' => 'cashier',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'cashier@example.com',
            'tenant_id' => $selectedTenant->id,
            'role' => 'cashier',
        ]);
    }

    public function test_master_can_create_tenant_user_with_generated_password(): void
    {
        $master = User::factory()->create([
            'role' => 'master',
            'tenant_id' => null,
        ]);
        $tenant = $this->tenant();

        $response = $this->actingAs($master)->post(route('master.tenants.users.store', $tenant), [
            'name' => 'Kitchen Staff',
            'email' => 'kitchen@example.com',
            'role' => 'kitchen',
            'generate_password' => '1',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('generated_password');

        $user = User::where('email', 'kitchen@example.com')->firstOrFail();

        $this->assertSame($tenant->id, $user->tenant_id);
        $this->assertTrue(Hash::check(session('generated_password'), $user->password));
    }

    public function test_master_role_cannot_be_created_from_tenant_management(): void
    {
        $master = User::factory()->create([
            'role' => 'master',
            'tenant_id' => null,
        ]);
        $tenant = $this->tenant();

        $this->actingAs($master)->post(route('master.tenants.users.store', $tenant), [
            'name' => 'Not Master',
            'email' => 'not-master@example.com',
            'role' => 'master',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'not-master@example.com',
        ]);
    }

    public function test_non_master_cannot_create_tenant_users(): void
    {
        $tenant = $this->tenant();
        $tenantUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'owner',
        ]);

        $this->actingAs($tenantUser)->post(route('master.tenants.users.store', $tenant), [
            'name' => 'Waiter',
            'email' => 'waiter@example.com',
            'role' => 'waiter',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'waiter@example.com',
        ]);
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
