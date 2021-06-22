<?php

namespace App\Events\Otc;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SellerApplyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sellerApply;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($seller_apply)
    {
        $this->sellerApply = $seller_apply;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
