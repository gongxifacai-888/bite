<?php


namespace App\Notifications;


use App\Channel\HuYiSMS;
use App\Channel\ModuEmail;
use App\Channel\MoEmail;
use App\Channel\ModuSMS;
use App\Channel\Websocket;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    use Queueable;
    const EMail =MoEmail::class;
    const SMS = ModuSMS::class;
    const MAIL = ModuEmail::class;
    const WEBSCOKET = Websocket::class;

    /**
     * Get the notification's delivery channels.
     *
     * @param User $user
     * @return array
     */
    public function via($user)
    {
       //       dd($user->email);
        // if ($user->mobile) {
        //     return [self::SMS];
        // }
        // $info=self::EMail;
        // dd($info);
        if ($user->email) {
            return [self::EMail];
        }
        return [];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->view('enter_your_mail_template');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {

    }

    /**获取短信模板数据
     * @param User $notifiable
     */
    public function toShortcode($notifiable)
    {

    }

//    public function toNexmo($notifiable)
//    {
//        return (new NexmoMessage())
//            ->content('验证码是123');
//    }


}
