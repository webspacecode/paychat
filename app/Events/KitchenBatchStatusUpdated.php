<?php

namespace App\Events;

use App\Models\Tenant\KitchenBatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class KitchenBatchStatusUpdated implements ShouldBroadcastNow
{
    public KitchenBatch $batch;

    public function __construct(KitchenBatch $batch)
    {
        $this->batch = $batch;
    }

    public function broadcastOn()
    {
        return new Channel('kitchen-orders');
    }

    public function broadcastAs()
    {
        return 'kitchen.batch.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'kitchen_batch',
            'id' => $this->batch->id,
            'order_id' => $this->batch->order_id,
            'batch_code' => $this->batch->batch_code,
            'status' => $this->batch->status,
            'location_id' => $this->batch->location_id,
            'table_session_id' => $this->batch->table_session_id,
            'updated_at' => $this->batch->updated_at,
        ];
    }
}
