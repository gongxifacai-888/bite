<?php


namespace App\Channel;


use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

/**普通郵件發送
 * Class ModuSMS
 * @package App\Channel
 */
class MoEmail
{

    protected $random    = '';
    protected $sms_sign  = '';

    public function __construct()
    {
        $this->config();
    }

    protected function config()
    {
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
       //var_dump($shrotcode['custom']);
        //$name = '王宝花';
        // Mail::send()的返回值为空，所以可以其他方法进行判断
        Mail::send('admin.test',['code'=>$shrotcode['custom']['code']],function($message) use($user,$shrotcode){
            $to = $user['email'];
            $message ->to($to)->subject($shrotcode['custom']['type']);
        });
        // 返回的一个错误数组，利用此可以判断是否发送成功
     //   dd(Mail::failures());
//        $params = [
//            'tel'        => [
//                'nationcode' => $user->area()->value('global_code'),
//                'mobile'     => $user->mobile,
//            ],
//            'signId'     => $this->sms_sign,
////            'templateId' => $shrotcode['type'],
//            'templateId' => '5f115451efb9a3723c6773c8',
//            'params'     => array_values($shrotcode['custom']),
//            'time'       => $time,
//            'ext'        => '',
//        ];
//        $params = $this->sign($params);
//        $res    = raw_http($this->url, json_encode($params), [
//            'accesskey' => $this->accesskey,
//            'random'    => $this->random,
//        ], ['Content-Type' => 'application/json'
//        ]);
//        throw_if(
//            !isset($res['result']) || $res['result'] != 0,
//            new \Exception($res['errmsg'] ?? "未知错误")
//        );
    }

    protected function sign($params)
    {
        $str           = "secretkey={$this->secretkey}&random={$this->random}&time={$params['time']}&mobile={$params['tel']['mobile']}";
        $sign          = hash('sha256', $str, false);
        $params['sig'] = $sign;
        return $params;
    }
}
