<?php

namespace App\Services\Strategies;

use App\Services\Contracts\CategoryStrategyInterface;


class CategoryStrategyResolver
{
    protected $strategies;

    public function __construct()
    {
        // Map industry => Strategy
        $this->strategies = [
            'default' => new DefaultCategoryStrategy(),
            // 'restaurant' => new RestaurantCategoryStrategy(),
            // 'ecommerce' => new EcommerceCategoryStrategy(),
        ];
    }

    public function resolve(string $industry): CategoryStrategyInterface
    {
        return $this->strategies[$industry] ?? $this->strategies['default'];
    }
}
