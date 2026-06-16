<?php

namespace App\Http\Middleware;

use App\Support\IndustryNormalizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIndustry
{
    public function handle(Request $request, Closure $next, string ...$allowed): Response
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (! $tenant) {
            abort(403, 'Industry unavailable.');
        }

        $industry = IndustryNormalizer::normalize($tenant->industry);
        $allowed = collect($allowed)
            ->map(fn ($value) => IndustryNormalizer::normalize($value))
            ->all();

        if (! in_array($industry, $allowed, true)) {
            abort(403, 'Industry unavailable.');
        }

        return $next($request);
    }
}
