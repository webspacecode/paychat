<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSelfPosEnabled;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Tests\TestCase;

class SelfPosAccessControlTest extends TestCase
{
    public function test_self_pos_is_enabled_by_default_for_existing_tenants(): void
    {
        $tenant = (new Tenant())->forceFill(['settings' => null]);

        $this->assertTrue($tenant->selfPosEnabled());
    }

    public function test_self_pos_can_be_disabled_and_reenabled_from_tenant_settings(): void
    {
        $tenant = (new Tenant())->forceFill([
            'settings' => ['self_pos_enabled' => false],
        ]);

        $this->assertFalse($tenant->selfPosEnabled());

        $tenant->settings = ['self_pos_enabled' => true];

        $this->assertTrue($tenant->selfPosEnabled());
    }

    public function test_self_pos_disabled_middleware_returns_support_response(): void
    {
        app()->instance('currentTenant', (new Tenant())->forceFill([
            'settings' => ['self_pos_enabled' => false],
        ]));

        $response = app(EnsureSelfPosEnabled::class)->handle(
            Request::create('/api/kiosk/demo/orders', 'POST'),
            fn () => response()->json(['ok' => true])
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('SELF_POS_DISABLED', $response->getData(true)['code']);
    }
}
