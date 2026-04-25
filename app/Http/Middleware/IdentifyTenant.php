<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('tenant_slug');

        if (preg_match('/^[A-Za-z0-9]{32}$/', $slug)) {
            // looks like hash → api_key
            $tenant = Tenant::where('api_key', $slug)->first();
        } else {
            // normal slug
            $tenant = Tenant::where('slug', $slug)->first();
        }

        if (!$tenant) {
            throw new NotFoundHttpException("Tenant not found.");
        }

        // Set Industry
        if ($tenant && !$request->has('industry')) {
            $request->merge([
                'industry' => $tenant->industry
            ]);
        }

        // Bind tenant globally
        app()->instance('currentTenant', $tenant);

        // Dynamically set tenant DB connection
        $base = config('database.connections.mysql');
        Config::set('database.connections.tenant', array_merge($base, [
            'database' => $tenant->database,
        ]));

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        DB::reconnect('tenant');

        return $next($request);
    }
}

