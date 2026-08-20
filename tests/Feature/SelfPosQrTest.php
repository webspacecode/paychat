<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Resource;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SelfPosQrTest extends TestCase
{
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        DB::connection('tenant')->reconnect();
        Config::set('services.frontend_url', 'http://localhost');

        Schema::connection('tenant')->create('resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->default('table');
            $table->string('area')->nullable();
            $table->string('floor')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status')->nullable();
            $table->integer('pos_x')->nullable();
            $table->integer('pos_y')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('shape')->nullable();
            $table->integer('rotation')->nullable();
            $table->integer('sort_order')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        $this->tenant = (new Tenant())->forceFill([
            'id' => 23,
            'slug' => 'frozen-cafe',
            'api_key' => '61OtxUm8aVglxPi38WJZOlffFTnrkfpZ',
            'industry' => 'cafe',
        ]);

        app()->instance('currentTenant', $this->tenant);
        Storage::fake('public');
    }

    public function test_tenant_self_pos_qr_first_generation_stores_file_and_returns_public_payload(): void
    {
        $response = $this->getJson('/api/frozen-cafe/self-pos/qr');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'target_url' => 'http://localhost/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ',
                'generated' => true,
            ])
            ->assertJsonStructure([
                'qr_public_url',
                'qr_svg',
                'download_url',
                'path',
            ]);

        Storage::disk('public')->assertExists('tenants/23/self-pos/self-pos.svg');
        $this->assertStringContainsString('<svg', $response->json('qr_svg'));
    }

    public function test_tenant_self_pos_qr_second_call_reuses_existing_file(): void
    {
        $first = $this->getJson('/api/frozen-cafe/self-pos/qr')->assertOk();
        $path = $first->json('path');
        $originalSvg = Storage::disk('public')->get($path);

        $second = $this->getJson('/api/frozen-cafe/self-pos/qr');

        $second->assertOk()->assertJson(['generated' => false]);
        $this->assertSame($originalSvg, Storage::disk('public')->get($path));
        $this->assertSame($originalSvg, $second->json('qr_svg'));
    }

    public function test_tenant_self_pos_qr_prefers_request_origin_over_configured_url(): void
    {
        Config::set('services.frontend_url', 'http://hardcoded.example');

        $response = $this
            ->withHeader('Origin', 'https://menu.example.com')
            ->getJson('/api/frozen-cafe/self-pos/qr');

        $response->assertOk()
            ->assertJson([
                'target_url' => 'https://menu.example.com/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ',
            ]);
    }

    public function test_tenant_self_pos_qr_uses_public_forwarded_host_over_localhost_origin(): void
    {
        Config::set('services.frontend_url', 'http://localhost:3000');

        $response = $this
            ->withHeader('Origin', 'http://localhost:3000')
            ->withHeader('X-Forwarded-Proto', 'https')
            ->withHeader('X-Forwarded-Host', 'paychat.shop')
            ->getJson('/api/frozen-cafe/self-pos/qr');

        $response->assertOk()
            ->assertJson([
                'target_url' => 'https://paychat.shop/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ',
            ]);
    }

    public function test_tenant_self_pos_qr_regenerates_old_file_without_target_metadata(): void
    {
        $path = 'tenants/23/self-pos/self-pos.svg';
        Storage::disk('public')->put($path, '<svg>old-hardcoded-domain</svg>');

        $response = $this
            ->withHeader('Origin', 'https://live-menu.example.com')
            ->getJson('/api/frozen-cafe/self-pos/qr');

        $response->assertOk()->assertJson(['generated' => true]);
        $this->assertNotSame('<svg>old-hardcoded-domain</svg>', Storage::disk('public')->get($path));
        $this->assertSame(
            'https://live-menu.example.com/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ',
            $response->json('target_url')
        );
    }

    public function test_tenant_self_pos_qr_refresh_regenerates_file(): void
    {
        $first = $this->getJson('/api/frozen-cafe/self-pos/qr')->assertOk();
        $path = $first->json('path');

        Storage::disk('public')->put($path, '<svg>old</svg>');

        $response = $this->getJson('/api/frozen-cafe/self-pos/qr?refresh=1');

        $response->assertOk()->assertJson(['generated' => true]);
        $this->assertNotSame('<svg>old</svg>', Storage::disk('public')->get($path));
        $this->assertStringContainsString('self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ', $response->json('target_url'));
    }

    public function test_table_self_pos_qr_returns_fresh_svg_and_does_not_store_file(): void
    {
        $table = Resource::create([
            'type' => 'table',
            'location_id' => 1,
            'name' => 'Table 1',
            'code' => 'T1',
            'area' => 'Main',
            'floor' => 'Ground',
            'capacity' => 4,
        ]);

        $response = $this->getJson("/api/frozen-cafe/tables/{$table->id}/self-pos-qr");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'target_url' => 'http://localhost/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ?table=T1',
                'table_reference' => 'T1',
                'table' => [
                    'id' => $table->id,
                    'name' => 'Table 1',
                    'code' => 'T1',
                    'area' => 'Main',
                    'floor' => 'Ground',
                    'capacity' => 4,
                ],
            ]);

        $this->assertStringContainsString('<svg', $response->json('qr_svg'));
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_table_self_pos_qr_prefers_request_origin_over_configured_url(): void
    {
        Config::set('services.frontend_url', 'http://hardcoded.example');

        $table = Resource::create([
            'type' => 'table',
            'location_id' => 1,
            'name' => 'Table 2',
            'code' => 'T2',
        ]);

        $response = $this
            ->withHeader('Origin', 'https://guest-menu.example.com')
            ->getJson("/api/frozen-cafe/tables/{$table->id}/self-pos-qr");

        $response->assertOk()
            ->assertJson([
                'target_url' => 'https://guest-menu.example.com/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ?table=T2',
            ]);
    }

    public function test_table_self_pos_qr_uses_public_forwarded_host_over_localhost_origin(): void
    {
        Config::set('services.frontend_url', 'http://localhost:3000');

        $table = Resource::create([
            'type' => 'table',
            'location_id' => 1,
            'name' => 'Table 3',
            'code' => 'T3',
        ]);

        $response = $this
            ->withHeader('Origin', 'http://localhost:3000')
            ->withHeader('X-Forwarded-Proto', 'https')
            ->withHeader('X-Forwarded-Host', 'paychat.shop')
            ->getJson("/api/frozen-cafe/tables/{$table->id}/self-pos-qr");

        $response->assertOk()
            ->assertJson([
                'target_url' => 'https://paychat.shop/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ?table=T3',
            ]);
    }

    public function test_table_self_pos_qr_falls_back_to_table_id_when_code_is_missing(): void
    {
        $table = Resource::create([
            'type' => 'table',
            'location_id' => 1,
            'name' => 'Patio Table',
            'code' => null,
        ]);

        $response = $this->getJson("/api/frozen-cafe/tables/{$table->id}/self-pos-qr");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'target_url' => "http://localhost/pos#/self-pos/61OtxUm8aVglxPi38WJZOlffFTnrkfpZ?table={$table->id}",
                'table_reference' => (string) $table->id,
            ]);
    }

    public function test_table_self_pos_qr_rejects_non_table_resources(): void
    {
        $room = Resource::create([
            'type' => 'room',
            'location_id' => 1,
            'name' => 'Private Room',
        ]);

        $this->getJson("/api/frozen-cafe/tables/{$room->id}/self-pos-qr")
            ->assertNotFound();
    }
}
