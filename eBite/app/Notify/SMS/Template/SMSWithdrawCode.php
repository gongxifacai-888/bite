<?php


namespace App\Notify\SMS\Template;


use App\Models\Setting\Setting;
use App\Notify\Notify;

class SMSWithdrawCode extends BaseSMSTemplate
{

    public $is_code = true;

    public function config()
    {
        $this->setContent([
            'type' => __('notify.申请提币')
        ]);
        $this->template_id = Setting::getValueByKey('sms_withdraw_template_id', '');
    }
}
