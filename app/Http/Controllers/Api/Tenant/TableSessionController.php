<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TableSession;
use App\Services\TableSessionService;
use Illuminate\Http\Request;

class TableSessionController extends Controller
{
    public function store(Request $request, TableSessionService $service)
    {
        $validated = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'table_id' => 'required|integer|exists:resources,id',
            'guest_count' => 'nullable|integer|min:1',
            'order_id' => 'nullable|integer|exists:pos_orders,id',
            'notes' => 'nullable|string',
        ]);

        $session = $service->create($validated);

        return response()->json([
            'message' => 'Table session created',
            'data' => $session,
        ], 201);
    }

    public function open(Request $request)
    {
        $sessions = TableSession::query()
            ->where('status', 'active')
            ->when($request->filled('location_id'), fn ($q) =>
                $q->where('location_id', $request->location_id)
            )
            ->with([
                'table',
                'order.items.product',
                'order.kitchenBatches.items.product',
                'order.table',
            ])
            ->latest('opened_at')
            ->get();

        return response()->json(['data' => $sessions]);
    }

    public function close(TableSession $session, TableSessionService $service)
    {
        $closed = $service->close($session);

        return response()->json([
            'message' => 'Table session closed',
            'data' => $closed,
        ]);
    }
}
