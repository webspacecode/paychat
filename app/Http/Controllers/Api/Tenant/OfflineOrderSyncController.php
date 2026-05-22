<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SyncOfflineOrderRequest;
use App\Services\OfflineOrderSyncService;
use App\Support\Observability;
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
            Observability::logFailure('offline.sync.failed', $e, [
                'tenant_slug' => $tenant->slug,
                'tenant_id' => $tenant->id,
                'location_id' => $request->input('location_id'),
                'action' => 'offline.sync',
            ], $request);

            return response()->json([
                'success' => false,
                'status' => 'failed',
                'local_order_id' => $request->input('local_order_id'),
                'message' => 'Offline order sync failed',
                'error' => $e->getMessage(),
                'support_code' => Observability::requestId($request),
            ], 422);
        }
    }
}
