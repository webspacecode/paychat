<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentQrGenerated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return new Channel('payment-qr');
    }

    public function broadcastAs()
    {
        return 'payment.qr.generated';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }
}
