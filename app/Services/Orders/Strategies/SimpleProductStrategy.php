<?php

namespace App\Services\Orders\Strategies;

use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\StockMovement;
use App\Services\Orders\Contracts\StockDeductionStrategy;
use Illuminate\Validation\ValidationException;

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
            $this->failStock("Inventory not found for product {$this->productName($product, $item->product_id)}");
        }

        if ($inventory->quantity < $item->quantity) {
            $this->failStock(
                "Insufficient stock for product {$this->productName($product, $item->product_id)}. Required: {$this->formatQuantity((float) $item->quantity)}, Available: {$this->formatQuantity((float) $inventory->quantity)}"
            );
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
