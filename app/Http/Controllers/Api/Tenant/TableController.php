<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Resource;
use App\Services\TableSessionService;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $tables = Resource::query()
            ->where('type', 'table')
            ->when($request->filled('location_id'), fn ($q) =>
                $q->where('location_id', $request->location_id)
            )
            ->when($request->filled('area'), fn ($q) =>
                $q->where('area', $request->area)
            )
            ->when($request->filled('floor'), fn ($q) =>
                $q->where('floor', $request->floor)
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status)
            )
            ->with('activeTableSession.order')
            ->orderBy('floor')
            ->orderBy('area')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $tables]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'area' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|string|max:50',
            'pos_x' => 'nullable|integer',
            'pos_y' => 'nullable|integer',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'shape' => 'nullable|string|in:rectangle,square,circle,booth,custom',
            'rotation' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'meta' => 'nullable|array',
        ]);

        $table = Resource::create(array_merge($validated, [
            'type' => 'table',
            'capacity' => $validated['capacity'] ?? 1,
            'status' => $validated['status'] ?? 'available',
        ]));

        return response()->json([
            'message' => 'Table created',
            'data' => $table,
        ], 201);
    }

    public function update(String $tenantSlug, String $tableId, Request $request)
    {
        $table = Resource::whereKey($tableId)->where('type', 'table')->firstOrFail();

        $validated = $request->validate([
            'location_id' => 'sometimes|integer|exists:locations,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:100',
            'area' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string|max:50',
            'pos_x' => 'nullable|integer',
            'pos_y' => 'nullable|integer',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'shape' => 'nullable|string|in:rectangle,square,circle,booth,custom',
            'rotation' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'meta' => 'nullable|array',
        ]);

        $table->update($validated);

        return response()->json([
            'message' => 'Table updated',
            'data' => $table->fresh(),
        ]);
    }

    public function updateStatus(String $tenantSlug, String $tableId, Request $request)
    {
        $table = Resource::whereKey($tableId)->where('type', 'table')->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $table->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Table status updated',
            'data' => $table->fresh(),
        ]);
    }

    public function release(String $tenantSlug, String $tableId, Request $request, TableSessionService $service)
    {
        $table = Resource::whereKey($tableId)->where('type', 'table')->firstOrFail();

        $released = $service->release($table, $request->boolean('force'));

        return response()->json([
            'message' => 'Table released',
            'data' => $released,
        ]);
    }
}
