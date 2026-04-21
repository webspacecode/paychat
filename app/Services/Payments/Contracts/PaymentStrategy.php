<?php

namespace App\Services\Payments\Contracts;

use App\Models\Order;

interface PaymentStrategy
{
    public function pay(Order $order, array $data);
}
