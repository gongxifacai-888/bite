<?php


namespace App\Channel;

use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**摩杜邮件
 * Class ModuEmail
 * @package App\Channel
 */
class ModuEmail
{
    protected $appkey    = '';
    protected $secretkey = '';
    protected $random    = '';
    protected $from_mail = '';
    protected $url       = 'https://live.moduyun.com/directmail/v1/singleSendMail';

    public function __construct()
    {
        $this->config();
    }

    protected function config()
    {
        $this->appkey    = Setting::getValueByKey('mail_appid', '5f1113fcefb9a3723c676e52');
        $this->secretkey = Setting::getValueByKey('mail_secretkey', '7f81cf6c6b914d9a93aa4489c1bb9999');
        $this->from_mail = Setting::getValueByKey('mail_from', 'ebitcex@ebitcex.com');
        $this->random    = rand(10000, 99999);
    }

    /**
     * @param User $user
     * @param BaseNotification $notification
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\Exception
     */
    public function send($user, Notification $notification)
    {
        
  
        $mailMessage = $notification->toMail($user);
      //  dd($mailMessage);
        $time        = time();
        $params      = [
            'ext'         => '',
            'fromAlias'   => 'admin',
            'htmlBody'    => $mailMessage->render(),
            'subject'     => $mailMessage->subject,
            'needToReply' => false,
            'clickTrace'  => '0',
            'readTrace'   => '0',
            'time'        => $time,
            'type'        => 0,
            'fromEmail'   => $this->from_mail,
            'toEmail'     => $user->email,
        ];
        $params      = $this->sign($params);

            $res = raw_http($this->url, json_encode($params), [
            'accesskey' => $this->appkey,
            'random'    => $this->random,
        ], [
            'Content-Type' => 'application/json'
        ]);
      // dd($res);
        throw_if(
            !isset($res['result']) || $res['result'] != 0,
            new \Exception($res['errmsg'] ?? "未知错误")
        );
    }

    protected function sign($params)
    {
        $str           = "secretkey={$this->secretkey}&random={$this->random}&time={$params['time']}&fromEmail={$params['fromEmail']}";
        $sign          = hash('sha256', $str, false);
        $params['sig'] = $sign;
        return $params;
    }
}
