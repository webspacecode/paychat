<?php

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant\Product;
use App\Services\ProductManagement\Contracts\ProductStrategyInterface;
use App\Services\ProductManagement\Strategies\ProductTypeStrategyResolver;

class RestaurantProductStrategy implements ProductStrategyInterface
{
    protected ProductTypeStrategyResolver $typeResolver;

    public function __construct()
    {
        $this->typeResolver = new ProductTypeStrategyResolver();
    }

    public function create(array $data): Product
    {
        $strategy = $this->typeResolver->resolve($data['type'] ?? 'basic');
        return $strategy->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $type = $data['type'] ?? $product->type;
        $strategy = $this->typeResolver->resolve($type);
        return $strategy->update($product, $data);
    }

    public function delete(Product $product): bool
    {
        $strategy = $this->typeResolver->resolve($product->type);
        return $strategy->delete($product);
    }

    public function search(?string $keyword = null, ?string $type = null)
    {
        $strategy = $this->typeResolver->resolve($type ?? 'basic');
        return $strategy->search($keyword, $type);
    }

    public function getById(int $id): ?Product
    {
        return Product::with(['images','units','recipe.items'])->find($id);
    }

    public function adjustInventory(Product $product, int $locationId, int $deltaQty, array $meta = [])
    {
        $strategy = $this->typeResolver->resolve($product->type);
        return $strategy->adjustInventory($product, $locationId, $deltaQty, $meta);
    }

    public function moveStock(Product $product, int $fromLocationId, int $toLocationId, int $quantity, array $meta = [])
    {
        $strategy = $this->typeResolver->resolve($product->type);
        return $strategy->moveStock($product, $fromLocationId, $toLocationId, $quantity, $meta);
    }
}
