<?php

namespace App\Events;

use App\Models\Tenant\KitchenBatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class KitchenBatchCreated implements ShouldBroadcastNow
{
    public KitchenBatch $batch;

    public function __construct(KitchenBatch $batch)
    {
        $this->batch = $batch;
        $this->batch->loadMissing(['order', 'table', 'items.product']);
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
            'location_id' => $this->batch->location_id,
            'table_session_id' => $this->batch->table_session_id,
            'table' => $table ? [
                'id' => $table->id,
                'name' => $table->name,
                'code' => $table->code,
            ] : null,
            'guest_count' => $order?->guest_count,
            'order_type' => $order?->order_type,
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
}
