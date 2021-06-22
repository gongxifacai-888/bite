<?php


namespace App\Notify\SMS;

use App\Models\Setting\Setting;
use App\Notify\Notify;
use App\Exceptions\ThrowException;
use App\Notify\NotifyTemplate;

class DuanXinBaoSMS extends Notify
{

    protected function config()
    {
        $this->send_type = self::SMS_DRIVER;
        $username = Setting::getValueByKey('smsbao_username');
        $password = Setting::getValueByKey('smsbao_password');
        $this->config['username'] = $username;
        $this->config['password'] = md5($password);
    }

    /**
     * @param $to
     * @param $content
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\Exception
     */
    public function send()
    {
        $to = $this->to;
        $content = NotifyTemplate::strReplace($this->template->template_id, $this->template->content);
        if ($this->area->global_code != 86) {
            $to = "+{$this->area->global_code} {$to}";
        }
        $url = $this->getGatewayUrl($this->area->global_code);
        $response = http($url, [
            'u' => $this->config['username'],
            'p' => $this->config['password'],
            'm' => $to,
            'c' => $content,
        ]);

        $status_arr = [
            "-1" => "参数不全",
            "-2" => "服务器不支持",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "44" => "账号被禁用",
            "50" => "敏感词限制",
        ];

        if ($response != 0) {
            throw new ThrowException($status_arr[$response]);
        }
    }

    protected function getGatewayUrl($global_code = 86)
    {
        if ($global_code == 86) {
            return 'http://api.smsbao.com/sms';
        } else {
            return 'https://api.smsbao.com/wsms';
        }
    }
}
