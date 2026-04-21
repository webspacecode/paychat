<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderCreated implements ShouldBroadcastNow
{
    public $order;
    public $token;

    public function __construct($order, $token)
    {
        // 🔥 IMPORTANT → include items relation
        $this->order = $order->load('items.product');
        $this->token = $token;
    }

    public function broadcastOn()
    {
        return new Channel('kitchen-orders');
    }

    public function broadcastAs()
    {
        return 'order.created';
    }
}