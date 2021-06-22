<?php

namespace App\Events\Market;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Entity\Market\Kline;

class KlineEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Kline
     */
    public $kline;


    /**
     * Create a new event instance.
     *
     * @param Kline $kline
     * @param bool  $kline
     *
     * @return void
     */
    public function __construct(Kline $kline)
    {
        $this->kline = $kline;
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
