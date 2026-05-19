<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\KitchenBatch;
use App\Models\Tenant\OrderToken;
use App\Services\KitchenBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KitchenQueueController extends Controller
{
    public function index(Request $request, KitchenBatchService $kitchenDates)
    {
        $businessDate = $request->get('business_date') ?: $kitchenDates->resolveBusinessDate();
        $locationId = $request->get('location_id');
        $status = $request->get('status');
        $source = $request->get('source', 'all');

        $items = collect();

        if (in_array($source, ['all', 'qsr'], true)) {
            $items = $items->merge($this->orderTokenItems($businessDate, $locationId, $status));
        }

        if (in_array($source, ['all', 'table_service'], true)) {
            $items = $items->merge($this->kitchenBatchItems($businessDate, $locationId, $status));
        }

        return response()->json([
            'business_date' => $businessDate,
            'data' => $items->sortBy('created_at')->values(),
        ]);
    }

    private function orderTokenItems(string $businessDate, $locationId, $status)
    {
        return OrderToken::query()
            ->with(['order.items.product', 'order.table'])
            ->whereHas('order', function ($query) use ($businessDate, $locationId) {
                $query->where('status', '!=', 'cancelled')
                    ->where(function ($q) {
                        $q->whereNull('dining_flow')
                            ->orWhere('dining_flow', '!=', 'table_service');
                    })
                    ->when($locationId, fn ($q) => $q->where('location_id', $locationId));

                if (Schema::hasColumn('pos_orders', 'business_date')) {
                    $query->whereDate('business_date', $businessDate);
                }
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when(!Schema::hasColumn('pos_orders', 'business_date'), fn ($q) =>
                $q->whereDate('token_date', $businessDate)
            )
            ->get()
            ->map(fn ($token) => $this->normalizeToken($token));
    }

    private function kitchenBatchItems(string $businessDate, $locationId, $status)
    {
        return KitchenBatch::query()
            ->with(['order', 'table', 'items.product'])
            ->whereDate('business_date', $businessDate)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->get()
            ->map(fn ($batch) => $this->normalizeBatch($batch));
    }

    private function normalizeToken(OrderToken $token): array
    {
        $order = $token->order;

        return [
            'type' => 'order_token',
            'id' => $token->id,
            'order_id' => $order?->id,
            'order_no' => $order?->order_no,
            'token_code' => $token->token_code,
            'batch_code' => null,
            'location_id' => $order?->location_id,
            'table' => null,
            'guest_count' => null,
            'order_type' => $order?->order_type,
            'dining_flow' => $order?->dining_flow ?? 'qsr',
            'status' => $token->status,
            'sent_at' => null,
            'created_at' => $token->created_at,
            'items' => $order ? $this->normalizeItems($order->items) : [],
        ];
    }

    private function normalizeBatch(KitchenBatch $batch): array
    {
        return [
            'type' => 'kitchen_batch',
            'id' => $batch->id,
            'order_id' => $batch->order_id,
            'order_no' => $batch->order?->order_no,
            'token_code' => null,
            'batch_code' => $batch->batch_code,
            'location_id' => $batch->location_id,
            'table' => $batch->table ? [
                'id' => $batch->table->id,
                'name' => $batch->table->name,
                'code' => $batch->table->code,
            ] : null,
            'guest_count' => $batch->order?->guest_count,
            'order_type' => $batch->order?->order_type,
            'dining_flow' => 'table_service',
            'status' => $batch->status,
            'sent_at' => $batch->sent_at,
            'created_at' => $batch->created_at,
            'items' => $this->normalizeItems($batch->items),
        ];
    }

    private function normalizeItems($items): array
    {
        return $items->map(fn ($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => optional($item->product)->name,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'total' => $item->total,
            'kitchen_status' => $item->kitchen_status,
            'item_status' => $item->item_status,
        ])->values()->all();
    }
}
