<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Setting\EditEvent::class => [
            \App\Listeners\Setting\EditListener::class,
        ],
    ];

    protected $subscribe = [
        \App\Listeners\Lever\LeverTransactionSubscriber::class,
        \App\Listeners\Market\MarketEventSubscriber::class,
        \App\Listeners\Micro\MicroOrderSubscriber::class,
        \App\Listeners\Socket\GatewayEventsSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
