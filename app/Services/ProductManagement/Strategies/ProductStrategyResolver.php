<?php

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant;
use App\Services\ProductManagement\Contracts\ProductStrategyInterface;
use App\Services\ProductManagement\Strategies\RestaurantProductStrategy;
use App\Services\ProductManagement\Strategies\RetailProductStrategy;

class ProductStrategyResolver
{
    public static function resolve(string $industry): ProductStrategyInterface
    {
        return match($industry) {
            'restaurant'    => new RestaurantProductStrategy(),
            'bakery'        => new RestaurantProductStrategy(),
            'cafe'        => new RestaurantProductStrategy(),
            'retail'        => new RetailProductStrategy(),
            default         => new RetailProductStrategy(),
        };
    }
}

