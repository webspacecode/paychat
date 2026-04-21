<?php

namespace App\Services\Orders\Strategies;

use App\Services\Orders\Contracts\StockDeductionStrategy;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\Recipe;
use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\StockMovement;

class RecipeProductStrategy implements StockDeductionStrategy
{
    public function deduct(OrderItem $item, $locationId)
    {
        $recipe = Recipe::where('product_id', $item->product_id)
            ->where(function ($q) use ($locationId) {
                $q->whereNull('location_id')
                  ->orWhere('location_id', $locationId);
            })
            ->first();
        
        if (!$recipe) {
            throw new \Exception("Recipe not found in the location for product {$item->product_id}");
        }

        foreach ($recipe->items as $recipeItem) {

            $requiredQty = $recipeItem->quantity * $item->quantity;

            $inventory = ProductInventory::where('product_id', $recipeItem->raw_product_id)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw new \Exception("Inventory not found for raw product {$recipeItem->raw_product_id}");
            }

            if ($inventory->quantity < $requiredQty) {
                throw new \Exception("Insufficient stock for raw product {$recipeItem->raw_product_id}");
            }

            // Deduct inventory
            $inventory->decrement('quantity', $requiredQty);

            // Record movement
            StockMovement::create([
                'product_id' => $recipeItem->raw_product_id,
                'from_location_id' => $locationId,
                'quantity' => $requiredQty,
                'type' => 'out',
                'order_id' => $item->order_id
            ]);
        }
    }
}