<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Location;
use Illuminate\Http\Request;

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
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Location created successfully',
            'data'    => $location
        ], 201);
    }

    /**
     * Display the specified location.
     */
    public function show($id)
    {
        $location = Location::findOrFail($id);

        return response()->json($location, 200);
    }

    /**
     * Update the specified location.
     */
    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'Location updated successfully',
            'data'    => $location
        ], 200);
    }

    /**
     * Remove the specified location.
     */
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully'
        ], 200);
    }
}
