<?php

namespace App\Events\Market;

use App\Entity\Market\Depth;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepthEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string
     */
    public $symbol;

    /**
     * @var Depth
     */
    public $depth;

    /**
     * @var int
     */
    public $currency_match_id;

    /**
     * @var int
     */
    public $market_from;

    /**
     * Create a new event instance.
     *
     * @param Depth $depth
     * @param int   $market_from
     *
     * @return void
     */
    public function __construct($depth, $market_from)
    {
        $this->depth = $depth;
        $this->market_from = $market_from;
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
