<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BakeryOrder;
use App\Models\Tenant\BakeryOrderPayment;
use App\Services\Bakery\BakeryPaymentService;
use Illuminate\Http\Request;

class BakeryOrderPaymentController extends Controller
{
    public function __construct(private BakeryPaymentService $payments)
    {
    }

    public function store(Request $request, string $tenantSlug, BakeryOrder $order)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'max:100'],
            'provider_ref' => ['nullable', 'string', 'max:150'],
            'paid_at' => ['nullable', 'date'],
            'upi_profile_id' => ['nullable', 'integer'],
            'generate_qr' => ['nullable', 'boolean'],
            'meta' => ['nullable', 'array'],
        ]);

        $validated['received_by'] = $request->user()?->id;
        $payment = $this->payments->recordPayment($order, $validated);

        return response()->json([
            'data' => $payment,
            'order' => $order->fresh(['payments', 'customer:id,name,phone,email', 'location:id,name']),
        ], 201);
    }

    public function markSuccess(Request $request, string $tenantSlug, BakeryOrder $order, BakeryOrderPayment $payment)
    {
        $validated = $request->validate([
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $validated['received_by'] = $request->user()?->id;
        $payment = $this->payments->markPaymentSuccess($order, $payment, $validated);

        return response()->json([
            'data' => $payment,
            'order' => $order->fresh(['payments', 'customer:id,name,phone,email', 'location:id,name']),
        ]);
    }
}
