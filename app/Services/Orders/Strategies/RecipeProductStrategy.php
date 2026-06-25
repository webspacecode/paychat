<?php

namespace App\Services\Orders\Strategies;

use App\Services\Orders\Contracts\StockDeductionStrategy;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\Recipe;
use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\StockMovement;
use Illuminate\Validation\ValidationException;

class RecipeProductStrategy implements StockDeductionStrategy
{
    public function deduct(OrderItem $item, $locationId)
    {
        $product = $item->product;

        if (!$product || !$product->track_inventory) {
            return;
        }

        $recipe = Recipe::with('items.rawProduct')
            ->where('product_id', $item->product_id)
            ->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                  ->orWhereNull('location_id');
            })
            ->orderByRaw('CASE WHEN location_id = ? THEN 0 ELSE 1 END', [$locationId])
            ->first();
        
        if (!$recipe) {
            $this->failStock("Recipe not found for {$this->productName($product, $item->product_id)} at this location");
        }

        foreach ($recipe->items as $recipeItem) {

            $requiredQty = $recipeItem->quantity * $item->quantity;
            $rawProductName = $this->productName($recipeItem->rawProduct, $recipeItem->raw_product_id);

            $inventory = ProductInventory::where('product_id', $recipeItem->raw_product_id)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $this->failStock("Inventory not found for raw product {$rawProductName}");
            }

            if ($inventory->quantity < $requiredQty) {
                $this->failStock(
                    "Insufficient stock for raw product {$rawProductName} used by {$this->productName($product, $item->product_id)}. Required: {$this->formatQuantity($requiredQty)}, Available: {$this->formatQuantity((float) $inventory->quantity)}"
                );
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

    private function failStock(string $message): void
    {
        throw ValidationException::withMessages([
            'stock' => $message,
        ]);
    }

    private function productName($product, int $productId): string
    {
        return $product?->name ?: "ID {$productId}";
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');
    }
}
