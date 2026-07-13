<?php

namespace App\Services\Orders;

use App\Models\Tenant\Order;

class OrderSideEffectPolicy
{
    public function forOrder(Order $order): array
    {
        // Phase 0 intentionally returns the exact existing behavior for every order.
        // Future sources can be introduced here without scattering order_type checks.
        return [
            'inventory' => true, 'kds' => true, 'token' => true, 'dine_in' => true,
            'display' => true, 'loyalty' => true, 'invoice' => true, 'reports' => true,
            'offline' => true,
        ];
    }
}
