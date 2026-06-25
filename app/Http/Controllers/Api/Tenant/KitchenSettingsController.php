<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Setting;
use App\Services\KitchenBatchService;
use App\Services\TenantSettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KitchenSettingsController extends Controller
{
    public function show()
    {
        return response()->json([
            'operation_mode' => app(KitchenBatchService::class)->operationMode(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'operation_mode' => ['required', 'string', Rule::in([
                KitchenBatchService::MODE_DEDICATED_KDS,
                KitchenBatchService::MODE_INLINE,
            ])],
        ]);

        Setting::set('kitchen_operation_mode', $validated['operation_mode'], 'string');

        app(TenantSettingsService::class)->forget();

        return response()->json([
            'operation_mode' => app(KitchenBatchService::class)->operationMode(),
        ]);
    }
}
