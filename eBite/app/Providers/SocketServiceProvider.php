<?php

namespace App\Providers;

use App\Logic\SocketPusher;
use Illuminate\Support\ServiceProvider;

class SocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        SocketPusher::$registerAddress = config('socket.register_address', '127.0.0.1:1236');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
