<?php

namespace App\Notifications\VerificationCode;

use App\Exceptions\ThrowException;
use App\Models\User\User;
use App\Notifications\BaseNotification;


class LoginCode extends BaseCode
{
    protected $type = '登陆';

    public function toMail($notifiable)
    {
        $user = User::where('mobile', $notifiable->mobile)->exists();
        if (!$user) {
            throw new ThrowException(__('notify.用户不存在'));
        }
        return parent::toMail($notifiable);
    }

    public function toShortcode($notifiable)
    {
        $user = User::where('mobile', $notifiable->mobile)->exists();
        if (!$user) {
            throw new ThrowException(__('notify.用户不存在'));
        }
        return parent::toShortcode($notifiable);
    }

}
