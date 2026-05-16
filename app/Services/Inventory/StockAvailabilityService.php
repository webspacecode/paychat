<?php

namespace App\Services\Inventory;

use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\Product;
use App\Models\Tenant\Recipe;
use Illuminate\Validation\ValidationException;

class StockAvailabilityService
{
    public function checkProductsStock(array $items, int $locationId): void
    {
        $requirements = [];

        foreach ($this->normalizeOrderItems($items) as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (float) $item['quantity'];
            $productName = $this->getProductDisplayName($productId);
            $recipe = $this->findRecipeForLocation($productId, $locationId);

            if ($recipe) {
                foreach ($recipe->items as $recipeItem) {
                    $rawProductId = (int) $recipeItem->raw_product_id;
                    $requirements[$rawProductId]['type'] = 'raw product';
                    $requirements[$rawProductId]['orders'][$productId] = $productName;
                    $requirements[$rawProductId]['quantity'] = ($requirements[$rawProductId]['quantity'] ?? 0)
                        + ($recipeItem->quantity * $quantity);
                }

                continue;
            }

            $requirements[$productId]['type'] = 'product';
            $requirements[$productId]['orders'][$productId] = $productName;
            $requirements[$productId]['quantity'] = ($requirements[$productId]['quantity'] ?? 0) + $quantity;
        }

        foreach ($requirements as $productId => $requirement) {
            $this->checkInventoryQuantity(
                (int) $productId,
                (float) $requirement['quantity'],
                $locationId,
                $requirement['type'],
                $this->formatOrderProductContext($requirement['orders'] ?? [])
            );
        }
    }

    public function checkProductStock(int $productId, float $quantity, int $locationId): void
    {
        $recipe = $this->findRecipeForLocation($productId, $locationId);

        if ($recipe) {
            foreach ($recipe->items as $recipeItem) {
                $this->checkInventoryQuantity(
                    (int) $recipeItem->raw_product_id,
                    (float) ($recipeItem->quantity * $quantity),
                    $locationId,
                    'raw product',
                    $this->formatOrderProductContext([$productId => $this->getProductDisplayName($productId)])
                );
            }

            return;
        }

        $this->checkInventoryQuantity(
            $productId,
            $quantity,
            $locationId,
            'product',
            $this->formatOrderProductContext([$productId => $this->getProductDisplayName($productId)])
        );
    }

    private function checkInventoryQuantity(int $productId, float $requiredQty, int $locationId, string $label, string $orderContext = ''): void
    {
        $productName = $this->getProductDisplayName($productId);

        $inventory = ProductInventory::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->first();

        if (!$inventory) {
            $this->fail("Inventory not found for {$label} {$productName}{$orderContext}");
        }

        if ($inventory->quantity < $requiredQty) {
            $this->fail(
                "Insufficient stock for {$label} {$productName}{$orderContext}. Required: {$this->formatQuantity($requiredQty)}, Available: {$this->formatQuantity($inventory->quantity)}"
            );
        }
    }

    private function findRecipeForLocation(int $productId, int $locationId): ?Recipe
    {
        return Recipe::with('items')
            ->where('product_id', $productId)
            ->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                    ->orWhereNull('location_id');
            })
            ->orderByRaw('CASE WHEN location_id = ? THEN 0 ELSE 1 END', [$locationId])
            ->first();
    }

    private function fail(string $message): void
    {
        throw ValidationException::withMessages([
            'stock' => $message,
        ]);
    }

    private function getProductDisplayName(int $productId): string
    {
        return Product::whereKey($productId)->value('name') ?? "ID {$productId}";
    }

    private function normalizeOrderItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($item) => isset($item['product_id'], $item['quantity']) && (float) $item['quantity'] > 0)
            ->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (float) $item['quantity'],
            ])
            ->values()
            ->all();
    }

    private function formatOrderProductContext(array $orderProducts): string
    {
        $names = array_values(array_unique(array_filter($orderProducts)));

        if (empty($names)) {
            return '';
        }

        return ' for order item '.implode(', ', $names);
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');
    }
}
