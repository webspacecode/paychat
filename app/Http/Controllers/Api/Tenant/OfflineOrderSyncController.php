<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SyncOfflineOrderRequest;
use App\Services\OfflineOrderSyncService;
use Illuminate\Http\JsonResponse;
use Throwable;

class OfflineOrderSyncController extends Controller
{
    public function sync(SyncOfflineOrderRequest $request, OfflineOrderSyncService $service): JsonResponse
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not resolved',
            ], 404);
        }

        try {
            $response = $service->sync($tenant, $request->validated());
            $status = ($response['status'] ?? null) === 'processing' ? 409 : 200;

            return response()->json($response, $status);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'local_order_id' => $request->input('local_order_id'),
                'message' => 'Offline order sync failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
