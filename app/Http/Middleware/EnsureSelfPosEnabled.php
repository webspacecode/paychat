<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSelfPosEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if ($tenant && method_exists($tenant, 'selfPosEnabled') && ! $tenant->selfPosEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Self POS is not enabled for this business. Please contact PayChat support to enable Self POS.',
                'code' => 'SELF_POS_DISABLED',
            ], 403);
        }

        return $next($request);
    }
}
