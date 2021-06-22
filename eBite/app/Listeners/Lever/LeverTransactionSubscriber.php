<?php

namespace App\Listeners\Lever;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LeverTransactionSubscriber
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
    public function handle($event)
    {
        //
    }

    /**
     * 为订阅者注册监听器.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\Lever\LeverSubmitOrderEvent::class,
            function () {}
        );
        $events->listen(
            \App\Events\Lever\LeverClosedEvent::class,
            TradeClosedListener::class
        );
    }
}
