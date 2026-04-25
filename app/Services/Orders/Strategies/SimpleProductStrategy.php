<?php

namespace App\Services\Orders\Strategies;

use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\StockMovement;
use App\Services\Orders\Contracts\StockDeductionStrategy;

class SimpleProductStrategy implements StockDeductionStrategy
{
    public function deduct(OrderItem $item, $locationId)
    {
        // 👇 Get product from item
        $product = $item->product;

        // 🚀 Skip inventory logic if not tracked
        if (!$product || !$product->track_inventory) {
            return;
        }
        
        // Get inventory row
        $inventory = ProductInventory::where('product_id', $item->product_id)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (!$inventory) {
            throw new \Exception("Inventory not found for product {$item->product_id}");
        }

        if ($inventory->quantity < $item->quantity) {
            throw new \Exception("Insufficient stock");
        }

        // Deduct quantity
        $inventory->decrement('quantity', $item->quantity);

        // Record stock movement
        StockMovement::create([
            'product_id' => $item->product_id,
            'from_location_id' => $locationId,
            'quantity' => $item->quantity,
            'type' => 'out',
        ]);
    }
}