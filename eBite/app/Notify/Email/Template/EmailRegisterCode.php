<?php


namespace App\Notify\Email\Template;

use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Notify\Notify;
use App\Exceptions\ThrowException;

class EmailRegisterCode extends BaseEmailTemplate
{

    public $is_code = true;

    /**
     * @throws \Exception
     */
    public function config()
    {
        $this->setContent([
            'type' => __('notify.注册')
        ]);
        $this->template_id = Setting::getValueByKey('email_register_template_id', '');
        $exists = User::where('email', $this->notify->to)->exists();
        if ($exists) {
            throw new ThrowException(__('notify.用户已注册'));
        }
    }
}
