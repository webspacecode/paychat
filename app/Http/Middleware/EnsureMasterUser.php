<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasterUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isMaster()) {
            abort(403, 'This dashboard is only available to the master account.');
        }

        return $next($request);
    }
}
