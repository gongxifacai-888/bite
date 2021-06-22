<?php


namespace App\Notify\SMS\Template;


use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Notify\Notify;
use Illuminate\Support\Str;
use App\Exceptions\ThrowException;

class SMSFindPasswordCode extends BaseSMSTemplate
{

    public $is_code = true;

    public function config()
    {
        $this->setContent([
            'type' => __('notify.找回密码')
        ]);
        $this->template_id = Setting::getValueByKey('sms_find_password_template_id', '');
        $user = User::where('mobile', $this->notify->to)->exists();
        if (!$user) {
            throw new ThrowException(__('notify.用户不存在'));
        }
    }

}
