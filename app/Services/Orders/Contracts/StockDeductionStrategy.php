<?php

namespace App\Services\Orders\Contracts;


use App\Models\Tenant\OrderItem;

interface StockDeductionStrategy
{
    public function deduct(OrderItem $item, $locationId);
}