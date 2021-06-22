<?php


namespace App\Notify\Email\Template;


use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Exceptions\ThrowException;

class EmailLoginCode extends BaseEmailTemplate
{

    public $is_code = true;

    /**
     * @throws \Exception
     */
    public function config()
    {
        $this->template_id = Setting::getValueByKey('email_login_template_id', '');
        $this->setContent([
            'type' => __('notify.登陆')
        ]);
        $user = User::where('email', $this->notify->to)->exists();
        if (!$user) {
            throw new ThrowException(__('notify.用户不存在'));
        }
    }
}
