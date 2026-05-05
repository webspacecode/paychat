<?php 

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Tenant\Product;
use App\Models\Tenant\Location;
use Illuminate\Support\LazyCollection;
use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\ProductManagement\Contracts\ProductStrategyInterface;

class DefaultProductStrategy implements ProductStrategyInterface
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {

            // 1. Create Product
            $payload = collect($data)
            ->only(['name', 'sku', 'type', 'price', 'unit', 'track_inventory'])
            ->all();

            $product = Product::create($payload);
    
            // 2. Save Images (handle file + string both)
            if (!empty($data['images']) && is_array($data['images'])) {

                foreach ($data['images'] as $img) {

                    if (!$img) {
                        continue;
                    }

                    $path = null;

                    // Case 1: Uploaded File
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                        $path = $img->store('products', 'public');
                    }

                    // Case 2: Already stored path OR filename string
                    elseif (is_string($img)) {

                        // If it's just a filename, normalize it
                        if (!str_contains($img, 'products/')) {
                            $path = 'products/images/' . ltrim($img, '/');
                        } else {
                            $path = $img;
                        }
                    }

                    if ($path) {
                        $product->images()->updateOrCreate(
                            ['image_path' => $path],
                            ['image_path' => $path]
                        );
                    }
                }
            }

            // 3. Save Inventory (location-wise)
            if (!empty($data['inventory']) && is_array($data['inventory'])) {
                foreach ($data['inventory'] as $inventory) {
                    $product->inventories()->updateOrCreate(
                        [
                            'location_id' => $inventory['location_id'],
                        ],
                        [
                            'quantity' => $inventory['quantity'] ?? 0,
                        ]
                    );
                }
            } else {

            }

             // Add Categories
            if (!empty($data['categories'])) {
                $this->syncCategories($product, $data['categories']);
            }

            return $product->load([
                'categories',
                'images',
                'inventories',
                'recipe.items'
            ]);
        });
    }

    public function getProductPayload(array $row): Array
    {
        // Debug Tenant DB
        // dd(DB::connection()->getDatabaseName());
        $defaultLocation = Location::where('type', 'default')->first();

        // ✅ Parse images column (comma separated)
        $images = [];
        if (!empty($row['images'])) {
            $images = collect(explode(',', $row['images']))
            ->map(fn ($img) => trim($img))
            ->filter()
            ->values()
            ->toArray();
        }

        // 🔥 build SAME payload as store() expects
        $product = [
            'name'  => $row['name'],
            'sku'   => $row['sku'],
            'type'  => $row['type'] ?? 'raw',
            'price' => isset($row['price']) ? (float) $row['price'] : null,
            'unit'  => $row['unit'] ?? null,
            'categories' => $row['categories'] ?? null,
            'images' => $images, // ✅ Added here
            'inventory' => !empty($row['location_id'])
                ? [[
                    'location_id' => (int) $row['location_id'],
                    'quantity'    => isset($row['quantity'])
                        ? (int) $row['quantity']
                        : 0,
                ]]
                : [['location_id' => $defaultLocation->id, 'quantity' => 0]],
        ];

        return $product;
    }


    protected function syncCategories(Product $product, ?string $categories)
    {
        if (empty($categories)) {
            return;
        }

        // Split by semicolon ;
        $names = collect(explode(';', $categories))
            ->map(fn ($c) => trim($c))
            ->filter();

        $categoryIds = [];

        foreach ($names as $name) {
            $category = Category::firstOrCreate(
                ['name' => Str::slug($name)],
                ['description' => $name]
            );

            $categoryIds[] = $category->id;
        }

        // Attach without removing existing ones
        $product->categories()->syncWithoutDetaching($categoryIds);
    }

    public function update(Product $product, array $data): Product
    {
        $payload = collect($data)->only(['name','type','price','unit'])->all();
        if (!empty($payload)) $product->update($payload);

        if (array_key_exists('images', $data)) {
            $product->images()->delete();
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                        // store file into storage/app/public/products
                        $path = $img->store('products', 'public');
                        $product->images()->create(['image_path' => $path]);
                    } elseif (is_string($img)) {
                        // fallback: accept string paths (maybe from API sync)
                        $product->images()->create(['image_path' => $img]);
                    }
                }
            }
        }
        return $product->fresh()->load(['images','inventories','recipe.items']);
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function search(string $keyword = null, ?string $type = null, ?int $locationId = null): Collection 
    {

        $q = Product::query()
            ->with(['images', 'categories:id,name,description']);

        // 🔍 Keyword Search
        if ($keyword) {
            $q->where(function ($w) use ($keyword) {
                $w->where('name', 'like', "%{$keyword}%")
                ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        // 🎯 MAIN BUSINESS LOGIC
        $q->where(function ($w) use ($locationId) {

            // ✅ 1. Recipe products (ALWAYS show)
            $w->where('type', 'recipe');

            // ✅ 2. Simple products (ONLY if stock available at location)
            $w->orWhere(function ($q2) use ($locationId) {

                $q2->where('type', 'basic')
                ->where(function ($q3) use ($locationId) {

                    // ✅ Case 1: No inventory tracking → ALWAYS include
                    $q3->where('track_inventory', 0);

                    // ✅ Case 2: Track inventory → check stock
                    $q3->orWhere(function ($q4) use ($locationId) {

                        $q4->where('track_inventory', 1);

                        if ($locationId) {
                            $q4->whereHas('inventories', function ($inv) use ($locationId) {
                                $inv->where('location_id', $locationId)
                                    ->where('quantity', '>', 0);
                            });
                        }
                    });
                });
            });

            // ❌ Raw products are automatically excluded
        });

        // 🔹 Optional: Explicit type filter (for admin/debug use)
        if ($type) {
            $q->where('type', $type);
        }

        return $q->orderBy('name')
                ->limit(200)
                ->get();
    }

    public function getById(int $id): ?Product
    {
        return Product::with(['images','inventories.location','recipe.items.rawProduct'])->find($id);
    }

    public function adjustInventory(Product $product, int $locationId, int $deltaQty, array $meta = []): ProductInventory
    {
        return DB::transaction(function () use ($product, $locationId, $deltaQty, $meta) {
            $inv = ProductInventory::lockForUpdate()->firstOrNew([
                'product_id' => $product->id,
                'location_id' => $locationId,
            ]);
            $newQty = (int)$inv->quantity + (int)$deltaQty;
            if ($newQty < 0) {
                throw ValidationException::withMessages(['quantity' => 'Insufficient stock at location']);
            }
            $inv->quantity = $newQty;
            $inv->save();

            StockMovement::create([
                'product_id' => $product->id,
                'from_location_id' => $deltaQty < 0 ? $locationId : null,
                'to_location_id'   => $deltaQty > 0 ? $locationId : null,
                'quantity' => abs($deltaQty),
                'type' => $deltaQty > 0 ? 'in' : 'out',
                'meta' => $meta,
            ]);

            return $inv;
        });
    }

    public function moveStock(Product $product, int $fromLocation, int $toLocation, int $quantity, array $meta = []): StockMovement
    {
        if ($quantity <= 0) throw ValidationException::withMessages(['quantity'=>'Quantity must be > 0']);
        if ($fromLocation === $toLocation) throw ValidationException::withMessages(['location'=>'Locations must differ']);

        return DB::transaction(function () use ($product,$fromLocation,$toLocation,$quantity,$meta) {
            $from = ProductInventory::lockForUpdate()->firstOrNew([
                'product_id'=>$product->id,'location_id'=>$fromLocation,
            ]);
            if ($from->quantity < $quantity) {
                throw ValidationException::withMessages(['quantity'=>'Not enough stock in source']);
            }
            $from->quantity -= $quantity; $from->save();

            $to = ProductInventory::lockForUpdate()->firstOrNew([
                'product_id'=>$product->id,'location_id'=>$toLocation,
            ]);
            $to->quantity += $quantity; $to->save();

            return StockMovement::create([
                'product_id'=>$product->id,
                'from_location_id'=>$fromLocation,
                'to_location_id'=>$toLocation,
                'quantity'=>$quantity,
                'type'=>'transfer',
                'meta'=>$meta,
            ]);
        });
    }
}
