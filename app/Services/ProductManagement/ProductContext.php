<?php

namespace App\Services\ProductManagement;

use App\Services\ProductManagement\Contracts\ProductStrategyInterface;

class ProductContext
{
    protected ProductStrategyInterface $strategy;

    public function __construct(ProductStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(ProductStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->strategy, $method], $args);
    }
}
