<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Services\BootstrapService;
use Illuminate\Http\Request;

class BootstrapController extends Controller
{
    public function __construct(private BootstrapService $bootstrap)
    {
    }

    public function show(Request $request)
    {
        return response()->json(
            $this->bootstrap->forUser($request->user(), app('currentTenant'))
        );
    }
}
