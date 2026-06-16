<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BakeryOrder;
use App\Services\Bakery\BakeryOrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BakeryOrderController extends Controller
{
    public function __construct(private BakeryOrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:150'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json(
            $this->orders->list($validated, (int) ($validated['per_page'] ?? 20))
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request, true);

        $order = $this->orders->create($validated, $request->user()?->id);

        return response()->json([
            'data' => $order,
        ], 201);
    }

    public function show(string $tenantSlug, BakeryOrder $order)
    {
        return response()->json([
            'data' => $order->load(['payments', 'customer:id,name,phone,email', 'location:id,name']),
        ]);
    }

    public function update(Request $request, string $tenantSlug, BakeryOrder $order)
    {
        $validated = $this->validatedPayload($request, false);

        $order = $this->orders->update($order, $validated, $request->user()?->id);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function updateStatus(Request $request, string $tenantSlug, BakeryOrder $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'max:50'],
        ]);

        $order = $this->orders->updateStatus($order, $validated['status'], $request->user()?->id);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function productionBoard(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        return response()->json([
            'data' => $this->orders->productionBoard($validated),
        ]);
    }

    private function validatedPayload(Request $request, bool $creating): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return $request->validate([
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'customer_id' => ['nullable', 'integer', 'exists:pos_customers,id'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'fulfillment_type' => ['nullable', 'string', Rule::in(['pickup', 'delivery'])],
            'fulfillment_at' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'max:50'],
            'flavour' => ['nullable', 'string', 'max:150'],
            'weight_value' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'string', 'max:20'],
            'cake_message' => ['nullable', 'string', 'max:255'],
            'design_notes' => ['nullable', 'string', 'max:5000'],
            'reference_image_path' => ['nullable', 'string', 'max:255'],
            'subtotal' => [$required, 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'advance_paid' => ['nullable', 'numeric', 'min:0'],
            'advance_payment_method' => ['nullable', 'string', 'max:50'],
            'advance_payment_status' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'max:100'],
            'provider_ref' => ['nullable', 'string', 'max:150'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
        ]);
    }
}
