<?php

namespace App\Services\ProductManagement;

use App\Models\Tenant\Product;
use App\Services\ProductManagement\Strategies\ProductStrategyResolver;

class ProductApplicationService
{
    public function __construct(private ProductStrategyResolver $resolver)
    {
    }

    public function create(array $validated): Product
    {
        return $this->resolver->resolve($validated['industry'])->create($validated);
    }

    public function update(Product $product, array $validated): Product
    {
        return $this->resolver->resolve($validated['industry'])->update($product, $validated);
    }

    public function createBasicNonInventory(array $validated): Product
    {
        return $this->create([...$validated, 'type' => 'basic', 'track_inventory' => false]);
    }
}
