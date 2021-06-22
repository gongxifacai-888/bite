<?php

namespace App\Listeners\Setting;

use App\Events\Setting\EditEvent;
use App\Models\User\Token;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EditListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(EditEvent $editEvent)
    {
        $setting = $editEvent->setting;

        $method = Str::studly($setting->key ?? '');
        $method = "set{$method}";

        if (method_exists($this, $method)) {
            $this->$method($setting);
        }
    }

    /**系统关闭
     *
     */
    public function setSystemOn($setting)
    {
        $value = $setting->value ?? 0;
        //如果系统关闭,删除所有token
        if (!$value) {
            Token::whereRaw(true)->delete();
        }
    }
}
