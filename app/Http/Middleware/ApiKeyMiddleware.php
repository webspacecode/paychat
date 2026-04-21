<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('x-api-key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API Key missing'
            ], 401);
        }

        $tenant = Tenant::where('api_key', $apiKey)->first();

        if (!$tenant) {
            return response()->json([
                'error' => 'Invalid API Key'
            ], 401);
        }

        // Attach tenant to request
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}