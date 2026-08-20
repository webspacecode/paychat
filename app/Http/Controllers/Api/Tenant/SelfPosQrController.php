<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Resource;
use App\Services\SelfPosQrService;
use Illuminate\Http\Request;

class SelfPosQrController extends Controller
{
    public function tenant(Request $request, SelfPosQrService $service)
    {
        return response()->json(
            $service->tenantQr(app('currentTenant'), $request, $request->boolean('refresh'))
        );
    }

    public function table(string $tenantSlug, string $table, Request $request, SelfPosQrService $service)
    {
        $resource = Resource::whereKey($table)->firstOrFail();

        return response()->json(
            $service->tableQr(app('currentTenant'), $resource, $request)
        );
    }
}
