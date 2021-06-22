<?php

namespace App\Notifications\VerificationCode;

use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DrawCode extends BaseCode
{
    protected $type = '提币';

}
