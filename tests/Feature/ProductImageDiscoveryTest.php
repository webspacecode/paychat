<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\ExternalProductImageSuggestion;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductImage;
use App\Services\ProductImages\ProductImageQueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageDiscoveryTest extends TestCase
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

        Config::set('services.pexels.enabled', true);
        Config::set('services.pexels.key', 'test-pexels-key');
        Config::set('services.pexels.base_url', 'https://api.pexels.test/v1');
        Cache::flush();
        Storage::fake('public');

        $this->tenant = (new Tenant())->forceFill([
            'id' => 23,
            'slug' => 'frozen-cafe',
            'api_key' => 'test-api-key',
            'industry' => 'cafe',
        ]);
        app()->instance('currentTenant', $this->tenant);

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_product_listing_does_not_call_external_image_provider(): void
    {
        Product::create([
            'name' => 'Cold Coffee',
            'sku' => 'CC-1',
            'type' => 'basic',
            'price' => 120,
            'track_inventory' => false,
            'is_active' => true,
        ]);
        Http::fake();

        $this->getJson('/api/frozen-cafe/products?industry=restaurant')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Cold Coffee']);

        Http::assertNothingSent();
    }

    public function test_suggestion_endpoint_returns_cached_suggestion_without_second_provider_call(): void
    {
        $product = $this->product();
        Http::fake([
            'api.pexels.test/v1/search*' => Http::response($this->pexelsPayload(111), 200),
        ]);

        $first = $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")
            ->assertOk()
            ->assertJson(['success' => true, 'cached' => false]);

        $second = $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")
            ->assertOk()
            ->assertJson(['success' => true, 'cached' => true]);

        $this->assertSame($first->json('suggestions.0.provider_image_id'), $second->json('suggestions.0.provider_image_id'));
        Http::assertSentCount(1);
    }

    public function test_refresh_triggers_new_provider_call_when_quota_allows(): void
    {
        $product = $this->product();
        Http::fake([
            'api.pexels.test/v1/search*' => Http::sequence()
                ->push($this->pexelsPayload(111), 200)
                ->push($this->pexelsPayload(222), 200),
        ]);

        $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")->assertOk();
        $response = $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions", ['refresh' => true])
            ->assertOk();

        $this->assertSame('222', $response->json('suggestions.0.provider_image_id'));
        Http::assertSentCount(2);
    }

    public function test_quota_exceeded_returns_429_without_provider_call(): void
    {
        Carbon::setTestNow('2026-08-20 10:15:00');
        $product = $this->product();
        Cache::put('product-image:23:pexels:hour:2026082010', 30, now()->addHour());
        Http::fake();

        $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")
            ->assertStatus(429)
            ->assertJson(['code' => 'PRODUCT_IMAGE_QUOTA_EXCEEDED']);

        Http::assertNothingSent();
    }

    public function test_product_with_merchant_image_does_not_call_provider_without_force(): void
    {
        $product = $this->product();
        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/cold-coffee.jpg',
            'source' => 'merchant_upload',
        ]);
        Http::fake();

        $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'has_image' => true,
            ]);

        Http::assertNothingSent();
    }

    public function test_accept_suggestion_downloads_and_stores_external_image(): void
    {
        $product = $this->product();
        Http::fake([
            'api.pexels.test/v1/search*' => Http::response($this->pexelsPayload(333), 200),
            'images.pexels.test/*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $suggestionId = $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")
            ->assertOk()
            ->json('suggestions.0.id');

        $response = $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions/{$suggestionId}/accept")
            ->assertOk()
            ->assertJson(['success' => true]);

        $image = ProductImage::first();
        $this->assertSame('external_approved', $image->source);
        $this->assertSame('pexels', $image->provider);
        $this->assertSame('333', $image->provider_image_id);
        $this->assertSame('accepted', ExternalProductImageSuggestion::first()->status);
        Storage::disk('public')->assertExists($image->image_path);
        $this->assertSame($image->url, $response->json('product.resolved_image_url'));
    }

    public function test_disabled_provider_returns_graceful_failure(): void
    {
        Config::set('services.pexels.enabled', false);
        $product = $this->product();
        Http::fake();

        $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")
            ->assertOk()
            ->assertJson([
                'success' => false,
                'code' => 'provider_unavailable',
            ]);

        $this->assertSame('failed', ExternalProductImageSuggestion::first()->status);
        Http::assertNothingSent();
    }

    public function test_provider_rate_limit_does_not_crash(): void
    {
        $product = $this->product();
        Http::fake([
            'api.pexels.test/v1/search*' => Http::response(['message' => 'rate limited'], 429),
        ]);

        $this->postJson("/api/frozen-cafe/products/{$product->id}/image-suggestions")
            ->assertOk()
            ->assertJson([
                'success' => false,
                'code' => 'provider_rate_limited',
            ]);

        $this->assertSame('failed', ExternalProductImageSuggestion::first()->status);
    }

    public function test_query_builder_is_deterministic(): void
    {
        $coffee = $this->product(['name' => 'Cold Coffee', 'sku' => 'CC-2']);
        $paneer = $this->product(['name' => 'Paneer Tikka', 'sku' => 'PT-1']);
        $paneer->categories()->attach($this->category('Indian'));
        $builder = new ProductImageQueryBuilder();

        $this->assertSame('cold coffee basic cafe food drink', $builder->build($coffee, $this->tenant));
        $this->assertSame('paneer tikka indian basic cafe food drink', $builder->build($paneer->fresh(), $this->tenant));
    }

    public function test_product_image_url_accessor_preserves_absolute_urls(): void
    {
        $external = new ProductImage(['image_path' => 'https://example.test/image.jpg']);
        $local = new ProductImage(['image_path' => 'products/image.jpg']);

        $this->assertSame('https://example.test/image.jpg', $external->url);
        $this->assertStringEndsWith('/storage/products/image.jpg', $local->url);
    }

    public function test_product_resolved_image_skips_missing_local_path_and_uses_external_fallback(): void
    {
        $product = $this->product();
        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/missing-local.jpg',
            'source' => 'merchant_upload',
        ]);
        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'https://images.pexels.test/fallback.jpg',
            'source' => 'external_approved',
            'provider' => 'pexels',
        ]);

        $fresh = $product->fresh('images');

        $this->assertSame('https://images.pexels.test/fallback.jpg', $fresh->resolved_image_url);
        $this->assertSame('external_approved', $fresh->resolved_image_source);
    }

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Cold Coffee',
            'sku' => 'CC-1',
            'type' => 'basic',
            'price' => 120,
            'track_inventory' => false,
            'is_active' => true,
        ], $overrides));
    }

    private function category(string $name)
    {
        return DB::table('categories')->insertGetId([
            'name' => strtolower($name),
            'description' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function pexelsPayload(int $id): array
    {
        return [
            'photos' => [[
                'id' => $id,
                'url' => "https://pexels.test/photo/{$id}",
                'photographer' => 'Pexels Creator',
                'photographer_url' => 'https://pexels.test/creator',
                'avg_color' => '#ffffff',
                'width' => 1200,
                'height' => 1200,
                'src' => [
                    'small' => "https://images.pexels.test/photos/{$id}/small.jpg",
                    'medium' => "https://images.pexels.test/photos/{$id}/medium.jpg",
                    'large' => "https://images.pexels.test/photos/{$id}/large.jpg",
                    'large2x' => "https://images.pexels.test/photos/{$id}/large2x.jpg",
                ],
            ]],
        ];
    }

    private function createSchema(): void
    {
        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->nullable();
            $table->string('type')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('image_path');
            $table->string('source')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_image_id')->nullable();
            $table->text('provider_url')->nullable();
            $table->string('author_name')->nullable();
            $table->text('author_url')->nullable();
            $table->string('license')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('external_product_image_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('provider');
            $table->string('query');
            $table->string('provider_image_id')->nullable();
            $table->text('preview_url')->nullable();
            $table->text('full_url')->nullable();
            $table->string('photographer_name')->nullable();
            $table->text('photographer_url')->nullable();
            $table->string('license')->nullable();
            $table->string('status')->default('suggested');
            $table->text('error_message')->nullable();
            $table->timestamp('searched_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('category_product', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('product_id');
        });

        Schema::connection('tenant')->create('product_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
}
