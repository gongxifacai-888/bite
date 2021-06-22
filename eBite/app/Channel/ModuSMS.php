<?php


namespace App\Channel;


use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Notification;

/**摩杜短信
 * Class ModuSMS
 * @package App\Channel
 */
class ModuSMS
{

    protected $accesskey = '';
    protected $secretkey = '';
    protected $random    = '';
    protected $sms_sign  = '';
    protected $url       = 'https://live.moduyun.com/sms/v2/sendsinglesms';

    public function __construct()
    {
        $this->config();
    }

    protected function config()
    {
        $this->accesskey = Setting::getValueByKey('sms_accesskey', '5f1113fcefb9a3723c676e52');
        $this->secretkey = Setting::getValueByKey('sms_secretkey', '7f81cf6c6b914d9a93aa4489c1bb9999');
        $this->sms_sign  = Setting::getValueByKey('sms_sign', '5f10fe97efb9a3723c676ca3');
        $this->random    = rand(10000, 99999);
    }

    /**
     * 发送
     * @param $user
     * @param Notification $notification
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function send($user, Notification $notification)
    {
        $shrotcode = $notification->toShortcode($user);
        $time   = time();
        $params = [
            'tel'        => [
                'nationcode' => $user->area()->value('global_code'),
                'mobile'     => $user->mobile,
            ],
            'signId'     => $this->sms_sign,
//            'templateId' => $shrotcode['type'],
            'templateId' => '5f115451efb9a3723c6773c8',
            'params'     => array_values($shrotcode['custom']),
            'time'       => $time,
            'ext'        => '',
        ];
        $params = $this->sign($params);
        $res    = raw_http($this->url, json_encode($params), [
            'accesskey' => $this->accesskey,
            'random'    => $this->random,
        ], ['Content-Type' => 'application/json'
        ]);
        throw_if(
            !isset($res['result']) || $res['result'] != 0,
            new \Exception($res['errmsg'] ?? "未知错误")
        );
    }

    protected function sign($params)
    {
        $str           = "secretkey={$this->secretkey}&random={$this->random}&time={$params['time']}&mobile={$params['tel']['mobile']}";
        $sign          = hash('sha256', $str, false);
        $params['sig'] = $sign;
        return $params;
    }
}
