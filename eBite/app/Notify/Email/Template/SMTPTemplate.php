<?php


namespace App\Notify\Email\Template;


class SMTPTemplate extends BaseEmailTemplate
{
    public $is_code = true;

    public function config()
    {
        $this->setContent([
            'type' => __('notify.注册')
        ]);
    }
}



