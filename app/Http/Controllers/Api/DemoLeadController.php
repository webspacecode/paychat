<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemoLead;
use Illuminate\Http\Request;

class DemoLeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'email' => 'nullable|email|max:255',

            'phone' => 'required|string|max:20',

            'business_name' => 'required|string|max:255',

            'business_type' => 'nullable|string|max:255',

            'counters' => 'nullable|string|max:255',

            'preferred_demo_time' => 'required|date',

        ]);

        $lead = DemoLead::create([

            'name' => $validated['name'],

            'email' => $validated['email'] ?? null,

            'phone' => $validated['phone'],

            'business_name' => $validated['business_name'],

            'business_type' => $validated['business_type'] ?? null,

            'counters' => $validated['counters'] ?? null,

            'preferred_demo_time' => $validated['preferred_demo_time'],

            'source' => 'website',

            'status' => 'new',

        ]);

        return response()->json([

            'success' => true,

            'message' => 'Demo request submitted successfully',

            'data' => $lead

        ], 201);
    }
}