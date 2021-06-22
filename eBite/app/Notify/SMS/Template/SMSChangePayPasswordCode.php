<?php


namespace App\Notify\SMS\Template;


use App\Models\Setting\Setting;
use App\Notify\Notify;

class SMSChangePayPasswordCode extends BaseSMSTemplate
{

    public $is_code = true;

    public function config()
    {
        $this->setContent([
            'type' => __('notify.找回支付密码')
        ]);
        $this->template_id = Setting::getValueByKey('sms_change_paypassword_template_id', '');
    }
}
