<?php

namespace App\Notifications\VerificationCode;


use App\Exceptions\ThrowException;
use App\Models\User\User;

class ForgotPasswordCode extends BaseCode
{
    // protected $type = '找回密码';
    protected $type = 'Modify password';
    // public function toMail($notifiable)
    // {
    //     $user = User::where('email', $notifiable->email)->exists();
    //     if (!$user) {
    //         throw new ThrowException(__('notify.用户不存在'));
    //     }
    //     return parent::toMail($notifiable);
    // }

    public function toShortcode($notifiable)
    {
        $user = User::where('email', $notifiable->email)->exists();
        if (!$user) {
            throw new ThrowException(__('notify.用户不存在'));
        }
        return parent::toShortcode($notifiable);
    }
}
