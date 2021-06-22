<?php

namespace App\Listeners\Market;

use App\Events\Market\DepthEvent;
use App\Logic\SocketPusher;
use App\Models\Currency\CurrencyMatch;


class DepthListener
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
     * @param object $event
     *
     * @return void
     */
    public function handle(DepthEvent $event)
    {
        try {
            $depth       = $event->depth;
            $market_from = $event->market_from;

            $currencyMatch = CurrencyMatch::findMatch($depth->currency_match_id);


            if ($currencyMatch->market_from != $market_from) {
                return;
            }
            SocketPusher::marketDepth($depth);
        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getFile());
            dump($t->getLine());
        }

    }
}
