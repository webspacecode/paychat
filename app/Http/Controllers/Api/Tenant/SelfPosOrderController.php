<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use App\Services\SelfPosOrderService;
use Illuminate\Http\Request;

class SelfPosOrderController extends Controller
{
    public function submit(string $tenantSlug, Order $order, Request $request, SelfPosOrderService $service)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,upi'],
            'amount' => ['nullable', 'numeric', 'gt:0'],
            'payment_id' => ['nullable', 'integer'],
            'upi_profile_id' => ['nullable', 'integer'],
            'customer' => ['nullable', 'array'],
            'customer.name' => ['nullable', 'string', 'max:150'],
            'customer.email' => ['nullable', 'email', 'max:150'],
            'customer.phone' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json($service->submit($order, $validated));
    }

    public function confirmPayment(string $tenantSlug, Order $order, Request $request, SelfPosOrderService $service)
    {
        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', 'in:cash,upi'],
        ]);

        return response()->json($service->confirmPayment($order, $validated['payment_method'] ?? null));
    }
}
