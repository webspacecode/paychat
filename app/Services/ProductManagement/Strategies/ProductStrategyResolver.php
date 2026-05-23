<?php

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant;
use App\Services\ProductManagement\Contracts\ProductStrategyInterface;
use App\Support\IndustryNormalizer;
use App\Services\ProductManagement\Strategies\RestaurantProductStrategy;
use App\Services\ProductManagement\Strategies\RetailProductStrategy;

class ProductStrategyResolver
{
    public static function resolve(?string $industry): ProductStrategyInterface
    {
        return match(IndustryNormalizer::normalize($industry)) {
            'restaurant'    => new RestaurantProductStrategy(),
            'bakery'        => new RestaurantProductStrategy(),
            'cafe'        => new RestaurantProductStrategy(),
            'retail'        => new RetailProductStrategy(),
            'services'      => new DefaultProductStrategy(),
            default         => new DefaultProductStrategy(),
        };
    }
}
