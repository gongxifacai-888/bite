<?php

namespace App\Listeners\Micro;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Console\Commands\Socket\Socket;
use App\Events\Micro\MicroClosedEvent;
use App\Logic\SocketPusher;

class MicroClosedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MicroClosedEvent $event)
    {
        SocketPusher::microClosed($event->microOrder);
    }
}
