<?php


namespace App\Channel;

use App\Models\User\User;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Notification;

class Websocket
{
    /**
     * @param User $user
     * @param BaseNotification $notification
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\Exception
     */
    public function send($user, Notification $notification)
    {
        dump($user->toArray());
        dump($notification);
    }

}
