<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiningStructureController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
        ]);

        $tables = Resource::query()
            ->where('type', 'table')
            ->where('location_id', $validated['location_id'])
            ->with('activeTableSession.order')
            ->orderBy('floor')
            ->orderBy('area')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $floors = $tables
            ->groupBy(fn ($table) => $table->floor ?: 'Default')
            ->map(function ($floorTables, $floor) {
                return [
                    'name' => $floor,
                    'areas' => $floorTables
                        ->groupBy(fn ($table) => $table->area ?: 'Main')
                        ->map(fn ($areaTables, $area) => [
                            'name' => $area,
                            'tables' => $areaTables->values(),
                        ])
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'location_id' => (int) $validated['location_id'],
            'floors' => $floors,
            'tables' => $tables,
        ]);
    }

    public function bulkUpsert(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'tables' => 'required|array|min:1',
            'tables.*.id' => 'nullable|integer|exists:resources,id',
            'tables.*.name' => 'required_without:tables.*.id|string|max:255',
            'tables.*.code' => 'nullable|string|max:100',
            'tables.*.area' => 'nullable|string|max:255',
            'tables.*.floor' => 'nullable|string|max:255',
            'tables.*.capacity' => 'nullable|integer|min:1',
            'tables.*.status' => 'nullable|string|max:50',
            'tables.*.pos_x' => 'nullable|integer',
            'tables.*.pos_y' => 'nullable|integer',
            'tables.*.width' => 'nullable|integer|min:1',
            'tables.*.height' => 'nullable|integer|min:1',
            'tables.*.shape' => 'nullable|string|in:rectangle,square,circle,booth,custom',
            'tables.*.rotation' => 'nullable|integer',
            'tables.*.sort_order' => 'nullable|integer',
            'tables.*.meta' => 'nullable|array',
        ]);

        $tables = DB::transaction(function () use ($validated) {
            return collect($validated['tables'])->map(function (array $payload) use ($validated) {
                $data = $this->tablePayload($payload, (int) $validated['location_id']);

                if (!empty($payload['id'])) {
                    $table = Resource::whereKey($payload['id'])
                        ->where('type', 'table')
                        ->where('location_id', $validated['location_id'])
                        ->firstOrFail();

                    $table->update($data);

                    return $table->fresh();
                }

                return Resource::create(array_merge($data, [
                    'type' => 'table',
                    'status' => $data['status'] ?? 'available',
                    'capacity' => $data['capacity'] ?? 1,
                ]));
            })->values();
        });

        return response()->json([
            'message' => 'Dining structure saved',
            'data' => $tables,
        ]);
    }

    public function updatePosition(String $tenantSlug, String $tableId, Request $request)
    {
        $table = Resource::whereKey($tableId)->where('type', 'table')->firstOrFail();

        $validated = $request->validate([
            'area' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'pos_x' => 'nullable|integer',
            'pos_y' => 'nullable|integer',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'shape' => 'nullable|string|in:rectangle,square,circle,booth,custom',
            'rotation' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);

        $table->update($validated);

        return response()->json([
            'message' => 'Table layout updated',
            'data' => $table->fresh(),
        ]);
    }

    private function tablePayload(array $payload, int $locationId): array
    {
        return collect($payload)
            ->only([
                'name',
                'code',
                'area',
                'floor',
                'capacity',
                'status',
                'pos_x',
                'pos_y',
                'width',
                'height',
                'shape',
                'rotation',
                'sort_order',
                'meta',
            ])
            ->merge(['location_id' => $locationId])
            ->filter(fn ($value) => $value !== null)
            ->all();
    }
}
