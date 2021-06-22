<?php


namespace App\Notify\Email\Template;


use App\Models\Setting\Setting;
use App\Notify\Notify;

class EmailChangePayPasswordCode extends BaseEmailTemplate
{

    public $is_code = true;

    public function config()
    {
        $this->setContent([
            'type' => __('notify.找回支付密码')
        ]);
        $this->template_id = Setting::getValueByKey('email_change_paypassword_template_id', '');
    }
}
