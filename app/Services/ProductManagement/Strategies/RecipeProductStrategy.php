<?php 

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant\Product;
use App\Models\Tenant\Recipe;
use App\Models\Tenant\RecipeItem;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Location;

class RecipeProductStrategy extends DefaultProductStrategy
{
    public function create(array $data): Product
    {
        $data['type'] = 'recipe';
        $items = (array)($data['items'] ?? []);
        $locationId = $data['location_id'] ?? null;

        return DB::transaction(function () use ($data,$items,$locationId) {
             // 1. Create Product
            $payload = collect($data)
                ->only(['name', 'sku', 'type', 'price', 'unit'])
                ->all();

            $product = parent::create($payload);

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
            }

             // Add Categories
            if (!empty($data['categories'])) {
                $this->syncCategories($product, $data['categories']);
            }

            $recipe = Recipe::create([
                'product_id' => $product->id,
                'location_id'=> $locationId,
                'description'=> $data['description'] ?? null,
            ]);

            foreach ($items as $it) {
                RecipeItem::create([
                    'recipe_id'     => $recipe->id,
                    'raw_product_id'=> $it['raw_product_id'],
                    'quantity'      => $it['quantity'],
                    'unit'          => $it['unit'] ?? null,
                ]);
            }

            return $product->load([
                'categories',
                'images',
                'inventories',
                'recipe.items'
            ]);
        });
    }

    private function getItemsData(?string $itemsString): array
    {
        if (empty($itemsString)) return [];

        $parts = array_filter(explode('|', $itemsString));

        // Extract SKUs
        $skus = collect($parts)
            ->map(fn($item) => trim(explode(':', $item)[0] ?? ''))
            ->filter()
            ->unique()
            ->values();

        // Fetch only required products
        $products = Product::whereIn('sku', $skus)
            ->pluck('id', 'sku');

        return collect($parts)
            ->map(function ($item) use ($products) {

                [$sku, $qty] = array_pad(explode(':', $item), 2, null);

                $sku = trim($sku);
                $qty = (float) $qty;

                if (!$sku || !$qty) {
                    throw new \Exception("Invalid format: $item");
                }

                if (!isset($products[$sku])) {
                    throw new \Exception("SKU not found: $sku");
                }

                return [
                    'raw_product_id' => $products[$sku],
                    'quantity' => $qty,
                ];
            })
            ->values()
            ->toArray();
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
            'items' => $this->getItemsData($row['items']),
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

    public function update(Product $product, array $data): Product
    {
        $items = array_key_exists('items',$data) ? (array)$data['items'] : null;
        $locationId = $data['location_id'] ?? null;

        return DB::transaction(function () use ($product,$data,$items,$locationId) {
            $product = parent::update($product, $data);

            $recipe = $product->recipe ?: Recipe::create([
                'product_id' => $product->id,
                'location_id'=> $locationId,
            ]);

            if (array_key_exists('description', $data) || $locationId) {
                $recipe->update([
                    'description' => $data['description'] ?? $recipe->description,
                    'location_id' => $locationId ?? $recipe->location_id,
                ]);
            }

            if (is_array($items)) {
                $recipe->items()->delete();
                foreach ($items as $it) {
                    RecipeItem::create([
                        'recipe_id'     => $recipe->id,
                        'raw_product_id'=> $it['raw_product_id'],
                        'quantity'      => $it['quantity'],
                        'unit'          => $it['unit'] ?? null,
                    ]);
                }
            }

            return $product->fresh()->load(['recipe.items.rawProduct','images']);
        });
    }
}
