<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\KitchenBatch;
use App\Models\Tenant\OrderToken;
use App\Services\KitchenBatchService;
use App\Support\Observability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KitchenQueueController extends Controller
{
    public function index(Request $request, KitchenBatchService $kitchenDates)
    {
        $startedAt = microtime(true);
        $businessDate = $request->get('business_date') ?: $kitchenDates->resolveBusinessDate();
        $locationId = $request->get('location_id');
        $status = $request->get('status');
        $source = $request->get('source', 'all');

        $items = collect();

        if (in_array($source, ['all', 'qsr'], true)) {
            $items = $items->merge($this->orderTokenItems($businessDate, $locationId, $status));
        }

        if (in_array($source, ['all', 'table_service'], true)) {
            $includeInline = $request->boolean('include_inline', false);

            $items = $items->merge($this->kitchenBatchItems(
                $businessDate,
                $locationId,
                $status,
                $includeInline,
                $kitchenDates->operationMode()
            ));
        }

        $typeCounts = $items->countBy('type');

        Observability::logInfo('kds.queue.loaded', [
            'business_date' => $businessDate,
            'location_id' => $locationId,
            'status' => $status,
            'source' => $source,
            'include_inline' => $request->boolean('include_inline', false),
            'queue_count' => $items->count(),
            'token_count' => (int) ($typeCounts['order_token'] ?? 0),
            'batch_count' => (int) ($typeCounts['kitchen_batch'] ?? 0),
            'duration_ms' => Observability::durationMs($startedAt),
        ], $request);

        return response()->json([
            'business_date' => $businessDate,
            'data' => $items->sortBy('created_at')->values(),
        ]);
    }

    private function orderTokenItems(string $businessDate, $locationId, $status)
    {
        return OrderToken::query()
            ->with(['order.items.product', 'order.location', 'order.table'])
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

    private function kitchenBatchItems(string $businessDate, $locationId, $status, bool $includeInline, string $operationMode)
    {
        if ($operationMode === KitchenBatchService::MODE_INLINE && !$includeInline) {
            return collect();
        }

        return KitchenBatch::query()
	            ->with(['order.tableSession.tables', 'table', 'tableSession.tables', 'tableSession.primaryTable', 'tableSession.linkedTables', 'items.product'])
	            ->whereDate('business_date', $businessDate)
	            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
	            ->when($status, fn ($q) => $q->where('status', $status))
	            ->when(!$status, fn ($q) => $q->whereNotIn('status', ['cancelled', 'canceled']))
	            ->get()
            ->map(fn ($batch) => $this->normalizeBatch($batch, $operationMode));
    }

    private function normalizeToken(OrderToken $token): array
    {
        $order = $token->order;

        if ($order && $order->payment_status === 'paid' && !$order->token) {
            Observability::logWarningMessage('kds.consistency.paid_order_missing_token', [
                'order_id' => $order->id,
                'location_id' => $order->location_id,
                'token_id' => $token->id,
            ]);
        }

        return [
            'type' => 'order_token',
            'id' => $order?->id,
            'order_id' => $order?->id,
            'token_id' => $token->id,
            'order_no' => $order?->order_no,
            'invoice_id' => $order?->invoice_id,
            'invoice_no' => $order?->invoice_no,
            'token_code' => $token->token_code,
            'token_number' => $token->token_number,
            'token' => [
                'id' => $token->id,
                'token_code' => $token->token_code,
                'token_number' => $token->token_number,
                'status' => $token->status,
            ],
            'batch_code' => null,
            'location_id' => $order?->location_id,
            'location' => [
                'id' => $order?->location_id,
                'name' => optional($order?->location)->name,
            ],
            'table' => $order?->table ? [
                'id' => $order->table->id,
                'name' => $order->table->name,
                'code' => $order->table->code,
            ] : null,
            'guest_count' => $order?->guest_count,
            'order_type' => $order?->order_type,
            'delivery_channel' => $order?->delivery_channel,
            'delivery_channel_label' => $order?->delivery_channel_label,
            'external_order_reference' => $order?->external_order_reference,
            'dining_flow' => $order?->dining_flow ?? 'qsr',
            'status' => $token->status,
            'order_status' => $order?->status,
            'payment_status' => $order?->payment_status,
            'sent_at' => null,
            'created_at' => $token->created_at,
            'updated_at' => $token->updated_at,
            'items' => $order ? $this->normalizeItems($order->items) : [],
        ];
    }

    private function normalizeBatch(KitchenBatch $batch, string $operationMode): array
    {
        $tableDisplay = $this->tableDisplay($batch);

        if (!$batch->tableSession || $batch->tableSession->status !== 'active') {
            Observability::logWarningMessage('kds.consistency.table_service_without_active_session', [
                'order_id' => $batch->order_id,
                'batch_id' => $batch->id,
                'table_id' => $batch->table_id,
                'table_session_id' => $batch->table_session_id,
            ]);
        }

        if (!$tableDisplay) {
            Observability::logWarningMessage('kds.consistency.batch_missing_table_display', [
                'order_id' => $batch->order_id,
                'batch_id' => $batch->id,
                'table_id' => $batch->table_id,
                'table_session_id' => $batch->table_session_id,
            ]);
        }

        return [
            'type' => 'kitchen_batch',
            'id' => $batch->id,
            'batch_id' => $batch->id,
            'order_id' => $batch->order_id,
            'order_no' => $batch->order?->order_no,
            'token_code' => null,
            'batch_code' => $batch->batch_code,
            'location_id' => $batch->location_id,
            'table_session_id' => $batch->table_session_id,
            'table_id' => $batch->table_id,
            'batch_number' => $batch->batch_number,
            'business_date' => $batch->business_date,
	            'kitchen_operation_mode' => $operationMode,
	            'table' => $batch->table ? [
                'id' => $batch->table->id,
                'name' => $batch->table->name,
                'code' => $batch->table->code,
            ] : null,
            'tables' => $this->tablePayloads($batch),
            'primary_table' => $this->primaryTablePayload($batch),
            'linked_tables' => $this->linkedTablePayloads($batch),
            'table_display' => $tableDisplay,
            'guest_count' => $batch->order?->guest_count,
            'order_type' => $batch->order?->order_type,
            'delivery_channel' => $batch->order?->delivery_channel,
            'delivery_channel_label' => $batch->order?->delivery_channel_label,
            'external_order_reference' => $batch->order?->external_order_reference,
	            'dining_flow' => 'table_service',
	            'status' => $batch->status,
	            'dispatch_channel' => $batch->dispatch_channel ?? 'board',
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
            'discount' => $item->discount ?? 0,
            'tax' => $item->tax ?? 0,
            'sku' => optional($item->product)->sku,
            'subtotal' => $item->subtotal,
            'total' => $item->total,
            'kitchen_status' => $item->kitchen_status,
            'kitchen_batch_id' => $item->kitchen_batch_id,
            'sent_to_kitchen_at' => $item->sent_to_kitchen_at,
            'item_status' => $item->item_status,
        ])->values()->all();
    }

    private function tablePayloads(KitchenBatch $batch)
    {
        $session = $batch->tableSession ?: $batch->order?->tableSession;

        if ($session) {
            $session->loadMissing(['tables']);

            return $session->tables
                ->map(fn ($table) => $this->tablePayload($table))
                ->filter()
                ->values()
                ->all();
        }

        return $batch->table ? [$this->tablePayload($batch->table)] : [];
    }

    private function primaryTablePayload(KitchenBatch $batch): ?array
    {
        $session = $batch->tableSession ?: $batch->order?->tableSession;

        if ($session) {
            $session->loadMissing(['primaryTable']);
            $table = $session->primaryTable;

            if ($table) {
                return $this->tablePayload($table);
            }
        }

        return $this->tablePayload($batch->table);
    }

    private function linkedTablePayloads(KitchenBatch $batch): array
    {
        $session = $batch->tableSession ?: $batch->order?->tableSession;

        if (! $session) {
            return [];
        }

        $session->loadMissing(['linkedTables']);

        return $session->linkedTables
            ->map(fn ($table) => $this->tablePayload($table))
            ->filter()
            ->values()
            ->all();
    }

    private function tableDisplay(KitchenBatch $batch): ?string
    {
        $session = $batch->tableSession ?: $batch->order?->tableSession;

        if ($session) {
            $session->loadMissing(['tables']);

            if ($session->table_display) {
                return $session->table_display;
            }
        }

        return $batch->table ? ($batch->table->name ?: $batch->table->code) : null;
    }

    private function tablePayload($table): ?array
    {
        if (! $table) {
            return null;
        }

        return [
            'id' => $table->id,
            'name' => $table->name,
            'code' => $table->code,
        ];
    }
}
