<?php

namespace App\Notifications\VerificationCode;

use App\Exceptions\ThrowException;
use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;

class RegisterCode extends BaseCode
{

    protected $type = 'Register';
    // protected $type = '注册';

    public function toMail($notifiable)
    {
   $exists = User::where('email', $notifiable->email)->exists();
        if ($exists) {
            throw new ThrowException(__('notify.用户已注册'));
        }
        return parent::toMail($notifiable);
    }

    public function toShortcode($notifiable)
    {
        $exists = User::where('mobile', $notifiable->mobile)->exists();
        if ($exists) {
            throw new ThrowException(__('notify.用户已注册'));
        }
        return parent::toShortcode($notifiable);
    }
}
