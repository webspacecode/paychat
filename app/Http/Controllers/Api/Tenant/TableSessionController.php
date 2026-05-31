<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TableSession;
use App\Services\TableSessionService;
use App\Support\Observability;
use Illuminate\Http\Request;

class TableSessionController extends Controller
{
    public function store(Request $request, TableSessionService $service)
    {
        $startedAt = microtime(true);
        $validated = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'table_id' => 'required|integer|exists:resources,id',
            'guest_count' => 'nullable|integer|min:1',
            'order_id' => 'nullable|integer|exists:pos_orders,id',
            'notes' => 'nullable|string',
        ]);

        $session = $service->create($validated);

        Observability::logInfo('table.session.created', [
            'location_id' => $session->location_id,
            'table_id' => $session->table_id,
            'table_session_id' => $session->id,
            'order_id' => $session->order_id,
            'guest_count' => $session->guest_count,
            'duration_ms' => Observability::durationMs($startedAt),
        ], $request);

        return response()->json([
            'message' => 'Table session created',
            'data' => $session,
        ], 201);
    }

    public function open(Request $request)
    {
        $startedAt = microtime(true);
        $sessions = TableSession::query()
            ->where('status', 'active')
            ->when($request->filled('location_id'), fn ($q) =>
                $q->where('location_id', $request->location_id)
            )
            ->with([
                'table',
                'tables',
                'primaryTable',
                'linkedTables',
                'order.items.product',
                'order.kitchenBatches.items.product',
                'order.table',
            ])
            ->latest('opened_at')
            ->get();

        Observability::logInfo('table.sessions.open_restored', [
            'location_id' => $request->input('location_id'),
            'session_count' => $sessions->count(),
            'order_count' => $sessions->pluck('order_id')->filter()->unique()->count(),
            'duration_ms' => Observability::durationMs($startedAt),
        ], $request);

        return response()->json(['data' => $sessions]);
    }

    public function close(Request $request, TableSession $session, TableSessionService $service)
    {
        $startedAt = microtime(true);
        $closed = $service->close($session);

        Observability::logInfo('table.session.closed', [
            'location_id' => $closed->location_id,
            'table_id' => $closed->table_id,
            'table_session_id' => $closed->id,
            'order_id' => $closed->order_id,
            'duration_ms' => Observability::durationMs($startedAt),
        ], $request);

        return response()->json([
            'message' => 'Table session closed',
            'data' => $closed,
        ]);
    }
}
