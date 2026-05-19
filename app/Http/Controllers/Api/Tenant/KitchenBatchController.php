<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\KitchenBatch;
use App\Services\KitchenBatchService;
use Illuminate\Http\Request;

class KitchenBatchController extends Controller
{
    public function updateStatus(Request $request, KitchenBatch $batch, KitchenBatchService $service)
    {
        $validated = $request->validate([
            'status' => 'required|in:waiting,preparing,ready,served,cancelled',
        ]);

        $batch = $service->updateStatus($batch, $validated['status']);

        return response()->json([
            'message' => 'Kitchen batch status updated',
            'data' => $batch,
        ]);
    }
}
