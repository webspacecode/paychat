<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Tenant\PosFavoriteProductController;
use App\Models\Tenant\Category;
use App\Models\Tenant\PosFavoriteProduct;
use App\Models\Tenant\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PosFavoriteProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        DB::connection('tenant')->reconnect();

        app()->instance('currentTenant', (object) [
            'id' => 11,
            'slug' => 'favorite-demo',
            'industry' => 'restaurant',
        ]);

        $this->createSchema();
    }

    public function test_adding_the_same_product_twice_is_idempotent(): void
    {
        $product = Product::create([
            'name' => 'Masala Tea',
            'sku' => 'TEA-1',
            'type' => 'basic',
            'price' => 20,
            'unit' => 'cup',
            'track_inventory' => false,
            'is_active' => true,
        ]);

        $controller = app(PosFavoriteProductController::class);

        $first = $controller->store(Request::create('/pos-favorites', 'POST', [
            'product_id' => $product->id,
        ]));
        $second = $controller->store(Request::create('/pos-favorites', 'POST', [
            'product_id' => $product->id,
        ]));

        $this->assertSame(201, $first->getStatusCode());
        $this->assertSame(200, $second->getStatusCode());
        $this->assertSame(1, PosFavoriteProduct::count());
        $this->assertSame($product->id, PosFavoriteProduct::first()->product_id);
        $this->assertSame(1, Product::count());
        $this->assertSame(0, Category::count());
    }

    public function test_delete_is_idempotent_and_product_delete_cascades(): void
    {
        $product = Product::create([
            'name' => 'Cold Coffee',
            'sku' => 'COFFEE-1',
            'type' => 'basic',
            'price' => 120,
            'unit' => 'glass',
            'track_inventory' => false,
            'is_active' => true,
        ]);

        PosFavoriteProduct::create([
            'product_id' => $product->id,
            'sort_order' => 1,
        ]);

        $controller = app(PosFavoriteProductController::class);

        $first = $controller->destroy('favorite-demo', $product->id);
        $second = $controller->destroy('favorite-demo', $product->id);

        $this->assertSame(200, $first->getStatusCode());
        $this->assertSame(200, $second->getStatusCode());
        $this->assertSame(0, PosFavoriteProduct::count());

        PosFavoriteProduct::create([
            'product_id' => $product->id,
            'sort_order' => 1,
        ]);

        $product->delete();

        $this->assertSame(0, PosFavoriteProduct::count());
    }

    private function createSchema(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('type')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unique(['product_id', 'category_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image_path');
            $table->timestamps();
        });

        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        (include database_path('migrations/tenant/2026_08_08_000001_create_pos_favorite_products_table.php'))->up();
    }
}
