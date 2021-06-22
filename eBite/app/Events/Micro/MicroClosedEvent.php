<?php

namespace App\Events\Micro;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Micro\MicroOrder;

class MicroClosedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $microOrder;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MicroOrder $micro_order)
    {
        $this->microOrder = $micro_order;
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
