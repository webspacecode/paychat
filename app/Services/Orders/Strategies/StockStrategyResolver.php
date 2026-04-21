<?php

namespace App\Services\Orders\Strategies;


use App\Services\Orders\Contracts\StockDeductionStrategy;
use App\Services\Orders\Strategies\RecipeProductStrategy;
use App\Services\Orders\Strategies\SimpleProductStrategy;
use App\Models\Tenant\Product;


class StockStrategyResolver
{
    public function resolve(Product $product): StockDeductionStrategy
    {
        if ($product->type === 'recipe') {
            return new RecipeProductStrategy();
        }

        return new SimpleProductStrategy();
    }
}