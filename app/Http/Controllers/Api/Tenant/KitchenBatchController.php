<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Events\KitchenBatchStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Tenant\KitchenBatch;
use App\Services\KitchenBatchService;
use Illuminate\Http\Request;

class KitchenBatchController extends Controller
{
    public function updateStatus(String $tenantSlug, String $batchId, Request $request, KitchenBatchService $service)
    {
        $validated = $request->validate([
            'status' => 'required|in:waiting,pending,preparing,ready,served,cancelled',
        ]);

        $batch = KitchenBatch::findOrFail($batchId);

        $batch = $service->updateStatus($batch, $validated['status']);

        event(new KitchenBatchStatusUpdated($batch));

        return response()->json([
            'message' => 'Kitchen batch status updated',
            'data' => $batch,
        ]);
    }
}
