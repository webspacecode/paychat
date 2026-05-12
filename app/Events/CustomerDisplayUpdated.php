<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerDisplayUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return new Channel('customer-display');
    }

    public function broadcastAs()
    {
        return 'customer.display.updated';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }
}