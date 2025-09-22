<?php 

namespace App\Services\ProductManagement\Strategies;

class ProductTypeStrategyResolver
{
    public function resolve(?string $type)
    {
        return match ($type) {
            'raw'        => new RawProductStrategy(),
            'recipe'     => new RecipeProductStrategy(),
            'basic'      => new DefaultProductStrategy(),
        };
    }
}