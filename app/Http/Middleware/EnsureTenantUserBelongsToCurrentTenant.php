<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUserBelongsToCurrentTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (! $user || ! $tenant) {
            abort(403, 'Tenant access denied.');
        }

        if ($user->isMaster()) {
            return $next($request);
        }

        if ((int) $user->tenant_id !== (int) $tenant->id) {
            abort(403, 'Tenant access denied.');
        }

        return $next($request);
    }
}
