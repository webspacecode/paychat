<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    public $order;
    public $token;

    public function __construct($order, $token)
    {
        $this->order = $order;
        $this->token = $token;
    }

    public function broadcastOn()
    {
        return new Channel('kitchen-orders');
    }

    public function broadcastAs()
    {
        return 'order.status.updated';
    }
}
