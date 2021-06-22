<?php

namespace App\Listeners\Lever;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Lever\LeverClosedEvent;
use App\Models\Lever\LeverTransaction;

class TradeClosedListener
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
    public function handle(LeverClosedEvent $event)
    {
        $lever_trades = $event->leverTrades;
        LeverTransaction::pushDeletedTrade($lever_trades);
    }
}
