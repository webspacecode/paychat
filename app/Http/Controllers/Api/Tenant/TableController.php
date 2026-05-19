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
            ->with('activeTableSession.order')
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
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|string|max:50',
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

    public function update(Request $request, Resource $table)
    {
        abort_unless($table->type === 'table', 404);

        $validated = $request->validate([
            'location_id' => 'sometimes|integer|exists:locations,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:100',
            'capacity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string|max:50',
            'meta' => 'nullable|array',
        ]);

        $table->update($validated);

        return response()->json([
            'message' => 'Table updated',
            'data' => $table->fresh(),
        ]);
    }

    public function updateStatus(Request $request, Resource $table)
    {
        abort_unless($table->type === 'table', 404);

        $validated = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $table->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Table status updated',
            'data' => $table->fresh(),
        ]);
    }

    public function release(Request $request, Resource $table, TableSessionService $service)
    {
        abort_unless($table->type === 'table', 404);

        $released = $service->release($table, $request->boolean('force'));

        return response()->json([
            'message' => 'Table released',
            'data' => $released,
        ]);
    }
}
