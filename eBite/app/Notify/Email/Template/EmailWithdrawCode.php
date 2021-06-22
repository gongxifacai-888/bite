<?php


namespace App\Notify\Email\Template;


use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Notify\Notify;
use App\Exceptions\ThrowException;

class EmailWithdrawCode extends BaseEmailTemplate
{

    public $is_code = true;

    public function config()
    {
        $this->setContent([
            'type' => __('notify.申请提币')
        ]);
        $this->template_id = Setting::getValueByKey('email_withdraw_template_id', '');
        $user = User::where('email', $this->notify->to)->exists();
        if (!$user) {
            throw new ThrowException(__('notify.用户不存在'));
        }
    }
}
