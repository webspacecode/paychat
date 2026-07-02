<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Events\KitchenBatchStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Tenant\KitchenBatch;
use App\Services\KitchenBatchService;
use App\Support\Observability;
use Illuminate\Http\Request;
use Throwable;

class KitchenBatchController extends Controller
{
    public function updateStatus(String $tenantSlug, String $batchId, Request $request, KitchenBatchService $service)
    {
        $startedAt = microtime(true);
        $validated = $request->validate([
            'status' => 'required|in:waiting,pending,preparing,ready,served,cancelled',
        ]);

        $batch = KitchenBatch::findOrFail($batchId);

        $batch = $service->updateStatus($batch, $validated['status']);

        try {
            event(new KitchenBatchStatusUpdated($batch));
        } catch (Throwable $e) {
            Observability::logFailure('kitchen.batch.status_broadcast.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'batch_id' => $batch->id,
                'order_id' => $batch->order_id,
                'location_id' => $batch->location_id,
                'status' => $batch->status,
                'error_code' => 'broadcast_failed',
            ], $request);
        }

        Observability::logInfo('kitchen.batch.status_updated', [
            'tenant_slug' => $tenantSlug,
            'batch_id' => $batch->id,
            'order_id' => $batch->order_id,
            'location_id' => $batch->location_id,
            'table_id' => $batch->table_id,
            'table_session_id' => $batch->table_session_id,
            'status' => $batch->status,
            'duration_ms' => Observability::durationMs($startedAt),
        ], $request);

        return response()->json([
            'message' => 'Kitchen batch status updated',
            'data' => $batch,
        ]);
    }

    public function reprint(String $tenantSlug, String $batchId, Request $request, KitchenBatchService $service)
    {
        $batch = KitchenBatch::with(['order.location', 'table', 'tableSession.tables', 'items.product'])->findOrFail($batchId);

        Observability::logInfo('kitchen.batch.reprint_requested', [
            'tenant_slug' => $tenantSlug,
            'batch_id' => $batch->id,
            'order_id' => $batch->order_id,
            'location_id' => $batch->location_id,
            'status' => $batch->status,
        ], $request);

        return response()->json([
            'message' => 'KOT print data ready',
            'batch' => $batch,
            'print_data' => $service->printPayload($batch),
        ]);
    }

    public function cancel(String $tenantSlug, String $batchId, Request $request, KitchenBatchService $service)
    {
        $startedAt = microtime(true);
        $batch = KitchenBatch::findOrFail($batchId);

        $batch = $service->cancelBatch($batch);

        try {
            event(new KitchenBatchStatusUpdated($batch));
        } catch (Throwable $e) {
            Observability::logFailure('kitchen.batch.cancel_broadcast.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'batch_id' => $batch->id,
                'order_id' => $batch->order_id,
                'location_id' => $batch->location_id,
                'error_code' => 'broadcast_failed',
            ], $request);
        }

        Observability::logInfo('kitchen.batch.cancelled', [
            'tenant_slug' => $tenantSlug,
            'batch_id' => $batch->id,
            'order_id' => $batch->order_id,
            'location_id' => $batch->location_id,
            'table_id' => $batch->table_id,
            'table_session_id' => $batch->table_session_id,
            'duration_ms' => Observability::durationMs($startedAt),
        ], $request);

        return response()->json([
            'message' => 'Kitchen batch cancelled',
            'data' => $batch,
        ]);
    }
}
