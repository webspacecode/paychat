<?php

namespace App\Events;

use App\Models\Tenant\KitchenBatch;
use App\Services\KitchenBatchService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class KitchenBatchCreated implements ShouldBroadcastNow
{
    public KitchenBatch $batch;

    public function __construct(KitchenBatch $batch)
    {
        $this->batch = $batch;
        $this->batch->loadMissing(['order.tableSession.tables', 'table', 'tableSession.tables', 'tableSession.primaryTable', 'tableSession.linkedTables', 'items.product']);
    }

    public function broadcastOn()
    {
        return new Channel('kitchen-orders');
    }

    public function broadcastAs()
    {
        return 'kitchen.batch.created';
    }

    public function broadcastWith(): array
    {
        $order = $this->batch->order;
        $table = $this->batch->table;

        return [
            'type' => 'kitchen_batch',
            'id' => $this->batch->id,
            'order_id' => $this->batch->order_id,
            'order_no' => $order?->order_no,
            'token_code' => null,
            'batch_code' => $this->batch->batch_code,
            'kitchen_operation_mode' => app(KitchenBatchService::class)->operationMode(),
            'location_id' => $this->batch->location_id,
            'table_session_id' => $this->batch->table_session_id,
            'table' => $table ? [
                'id' => $table->id,
                'name' => $table->name,
                'code' => $table->code,
            ] : null,
            'tables' => $this->tablePayloads(),
            'primary_table' => $this->primaryTablePayload(),
            'linked_tables' => $this->linkedTablePayloads(),
            'table_display' => $this->tableDisplay(),
            'guest_count' => $order?->guest_count,
            'order_type' => $order?->order_type,
            'delivery_channel' => $order?->delivery_channel,
            'delivery_channel_label' => $order?->delivery_channel_label,
            'external_order_reference' => $order?->external_order_reference,
            'dining_flow' => 'table_service',
            'status' => $this->batch->status,
            'sent_at' => $this->batch->sent_at,
            'created_at' => $this->batch->created_at,
            'items' => $this->batch->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => optional($item->product)->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->total,
                'kitchen_status' => $item->kitchen_status,
                'kitchen_batch_id' => $item->kitchen_batch_id,
                'sent_to_kitchen_at' => $item->sent_to_kitchen_at,
                'item_status' => $item->item_status,
            ])->values()->all(),
        ];
    }

    private function tablePayloads(): array
    {
        $session = $this->batch->tableSession ?: $this->batch->order?->tableSession;

        if ($session) {
            $session->loadMissing(['tables']);

            return $session->tables
                ->map(fn ($table) => $this->tablePayload($table))
                ->filter()
                ->values()
                ->all();
        }

        return $this->batch->table ? [$this->tablePayload($this->batch->table)] : [];
    }

    private function primaryTablePayload(): ?array
    {
        $session = $this->batch->tableSession ?: $this->batch->order?->tableSession;

        if ($session) {
            $session->loadMissing(['primaryTable']);
            $table = $session->primaryTable;

            if ($table) {
                return $this->tablePayload($table);
            }
        }

        return $this->tablePayload($this->batch->table);
    }

    private function linkedTablePayloads(): array
    {
        $session = $this->batch->tableSession ?: $this->batch->order?->tableSession;

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

    private function tableDisplay(): ?string
    {
        $session = $this->batch->tableSession ?: $this->batch->order?->tableSession;

        if ($session) {
            $session->loadMissing(['tables']);

            if ($session->table_display) {
                return $session->table_display;
            }
        }

        return $this->batch->table ? ($this->batch->table->name ?: $this->batch->table->code) : null;
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
