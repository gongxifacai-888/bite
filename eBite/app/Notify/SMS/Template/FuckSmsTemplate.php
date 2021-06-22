<?php


namespace App\Notify\SMS\Template;


use App\Models\Setting\Setting;

class FuckSmsTemplate extends BaseSMSTemplate
{
    public $is_code = true;

    public function config()
    {
        $this->setContent([
            'type' => __('notify.注册'),
        ]);
    }
}
