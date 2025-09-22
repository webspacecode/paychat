<?php 

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant\Product;
use App\Models\Tenant\Recipe;
use App\Models\Tenant\RecipeItem;
use Illuminate\Support\Facades\DB;

class RecipeProductStrategy extends DefaultProductStrategy
{
    public function create(array $data): Product
    {
        $data['type'] = 'recipe';
        $items = (array)($data['items'] ?? []);
        $locationId = $data['location_id'] ?? null;

        return DB::transaction(function () use ($data,$items,$locationId) {
            $product = parent::create($data);

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

            return $product->load(['recipe.items.rawProduct','images']);
        });
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
