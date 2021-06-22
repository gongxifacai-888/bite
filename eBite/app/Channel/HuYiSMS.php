<?php


namespace App\Channel;

use App\Models\Setting\Setting;
use App\Exceptions\ThrowException;
use App\Models\User\User;
use App\Notifications\BaseNotification;
use App\Notify\NotifyTemplate;
use Illuminate\Notifications\Notification;

/**互亿无线短信发送
 * Class HuYiSMS
 * @package App\Channel
 */
class HuYiSMS
{

    const STATUS_OK = 2;

    protected $config = [];

    public function __construct()
    {
        $this->config();
    }

    protected function config()
    {
        $this->config['domestic_sms_appid'] = Setting::getValueByKey('domestic_sms_appid');
        $this->config['domestic_sms_appkey'] = Setting::getValueByKey('domestic_sms_appkey');
        $this->config['overseas_sms_appid'] = Setting::getValueByKey('overseas_sms_appid');
        $this->config['overseas_sms_appkey'] = Setting::getValueByKey('overseas_sms_appkey');
    }

    /**
     * @param User $user
     * @param BaseNotification $notification
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\Exception
     */
    public function send($user, Notification $notification)
    {
        $shortcode = $notification->toShortcode($user);

        if ($user->area()->value('global_code') == 86) {
            $appid = $this->config['domestic_sms_appid'];
            $appkey = $this->config['domestic_sms_appkey'];
        } else {
            $appid = $this->config['overseas_sms_appid'];
            $appkey = $this->config['overseas_sms_appkey'];
        }
        $url = $this->getGatewayUrl($user);
        $content = $this->strReplace($shortcode['type'], $shortcode['custom']);
        $time = now();
        $response = http($url, [
            'account' => $appid,
            'password' => $appkey,
            'time' => $time->getTimestamp(),
            'format' => 'json',
            'mobile' => $user->mobile,
            'content' => $content,
        ], 'POST');
        $status_arr = [
            0 => '提交失败',
            2 => '提交成功',
            400 => '非法ip访问',
            401 => '帐号不能为空',
            402 => '密码不能为空',
            403 => '手机号码不能为空',
            4030 => '手机号码已被列入黑名单',
            404 => '短信内容不能为空',
            405 => 'API ID或API KEY不正确',
            4050 => '账号被冻结',
            40501 => '动态密码已过期',
            40502 => '动态密码校验失败',
            4051 => '剩余条数不足',
            4052 => '访问ip与备案ip不符',
            406 => '手机号码格式不正确',
            407 => '短信内容含有敏感字符',
            4070 => '签名格式不正确',
            4071 => '没有提交备案模板',
            4072 => '提交的短信内容与审核通过的模板内容不匹配',
            40722 => '变量内容超过指定的长度【8】',
            4073 => '短信内容超出长度限制',
            4074 => '短信内容包含emoji符号',
            4075 => '签名未通过审核',
            408 => '发送超限（[20]条），已加入黑名单，可登入平台解除',
            4080 => '同一手机号码同一秒钟之内发送频率不能超过1条',
            4082 => '超出同一手机号一天之内【5】条短信限制',
            4085 => '同一手机号验证码短信发送超出【5】条',
        ];

        if ($response['code'] != self::STATUS_OK) {
            throw new ThrowException($status_arr[$response['code']] ?? "未知错误");
        }
    }

    protected function getGatewayUrl($user)
    {
        if ($user->area()->value('global_code') == 86) {
            return 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';
        } else {
            return 'http://api.isms.ihuyi.com/webservice/isms.php?method=Submit';
        }
    }

    /**
     * @param $template_str
     * @param $param
     * @return string|string[]|null
     * @throws \Exception
     */
    public function strReplace($template_str, $param)
    {
        $need_param = [];
        $match_count = preg_match_all('/\@var\(([a-zA-Z_]\w*)\)/', $template_str, $need_param);
        if ($match_count > 0 && count(reset($need_param)) > count($param)) {
            throw new \Exception('所需参数不匹配');
        }
        $diff = array_diff(next($need_param), array_keys($param));
        if (count($diff) > 0) {
            throw new \Exception($template_str . '缺少参数：' . implode(',', $diff));
        }
        return preg_replace_callback('/\@var\(([a-zA-Z_]\w*)\)/', function ($matches) use ($param) {
            extract($param);
            $value = $matches[1];
            return $$value ?? '';
        }, $template_str);
    }
}
