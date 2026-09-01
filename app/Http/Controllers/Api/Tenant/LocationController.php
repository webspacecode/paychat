<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function index()
    {
        return response()->json(Location::all(), 200);
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'type' => ['nullable', 'string', 'max:80'],
            'business_day_enabled' => ['nullable', 'boolean'],
            'business_day_start_time' => ['nullable', 'date_format:H:i'],
            'business_day_end_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['nullable', 'string', 'max:80', Rule::in($this->allowedTimezones())],
        ]);

        $location = Location::create($this->locationPayload($validated));

        return response()->json([
            'message' => 'Location created successfully',
            'data'    => $location
        ], 201);
    }

    /**
     * Display the specified location.
     */
    public function show($tenantSlug, $id)
    {
        $location = Location::findOrFail($id);

        return response()->json($location, 200);
    }

    /**
     * Update the specified location.
     */
    public function update(Request $request, $tenantSlug, $id)
    {
        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'type' => ['nullable', 'string', 'max:80'],
            'business_day_enabled' => ['nullable', 'boolean'],
            'business_day_start_time' => ['nullable', 'date_format:H:i'],
            'business_day_end_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['nullable', 'string', 'max:80', Rule::in($this->allowedTimezones())],
        ]);

        $location->update($this->locationPayload($validated));

        return response()->json([
            'message' => 'Location updated successfully',
            'data'    => $location
        ], 200);
    }

    public function updateBusinessDayTiming(Request $request)
    {
        $validated = $request->validate([
            'location_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'business_day_enabled' => ['nullable', 'boolean'],
            'business_day_start_time' => ['nullable', 'date_format:H:i'],
            'business_day_end_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['nullable', 'string', 'max:80', Rule::in($this->allowedTimezones())],
        ]);

        $location = $this->resolveTimingLocation($validated);

        if (! $location) {
            return response()->json([
                'message' => 'Location not found. Please refresh locations and select a valid outlet.',
                'code' => 'LOCATION_NOT_FOUND',
            ], 404);
        }

        $location->update($this->locationPayload(array_merge([
            'name' => $location->name,
            'address' => $location->address,
            'type' => $location->type,
        ], $validated)));

        return response()->json([
            'message' => 'Business day timing updated successfully',
            'data' => $location->fresh(),
        ], 200);
    }

    /**
     * Remove the specified location.
     */
    public function destroy($tenantSlug, $id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully'
        ], 200);
    }

    private function locationPayload(array $validated): array
    {
        $payload = collect($validated)
            ->only(['name', 'address', 'type'])
            ->all();

        foreach (['business_day_enabled', 'business_day_start_time', 'business_day_end_time', 'timezone'] as $column) {
            if (array_key_exists($column, $validated) && Schema::hasColumn('locations', $column)) {
                $payload[$column] = $column === 'timezone'
                    ? $this->normalizeTimezone($validated[$column])
                    : $validated[$column];
            }
        }

        if (array_key_exists('business_day_enabled', $payload) && ! $payload['business_day_enabled']) {
            if (Schema::hasColumn('locations', 'business_day_start_time')) {
                $payload['business_day_start_time'] = null;
            }

            if (Schema::hasColumn('locations', 'business_day_end_time')) {
                $payload['business_day_end_time'] = null;
            }
        }

        return $payload;
    }

    private function allowedTimezones(): array
    {
        return array_values(array_unique(array_merge(timezone_identifiers_list(), [
            'Asia/Calcutta',
        ])));
    }

    private function normalizeTimezone(?string $timezone): ?string
    {
        return $timezone === 'Asia/Calcutta' ? 'Asia/Kolkata' : $timezone;
    }

    private function resolveTimingLocation(array $validated): ?Location
    {
        if (! empty($validated['location_id'])) {
            $location = Location::find($validated['location_id']);

            if ($location) {
                return $location;
            }
        }

        if (! empty($validated['name'])) {
            return Location::where('name', $validated['name'])->first();
        }

        return null;
    }
}
