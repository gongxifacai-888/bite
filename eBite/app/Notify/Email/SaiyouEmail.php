<?php


namespace App\Notify\Email;

use App\Models\Setting\Setting;
use App\Notify\Email;
use App\Notify\Notify;
use App\Exceptions\ThrowException;

class SaiyouEmail extends Notify
{

    public function config()
    {
        $this->send_type = self::EMAIL_DRIVER;
        $this->config['saiyou_mail_appid'] = Setting::getValueByKey('saiyou_mail_appid');
        $this->config['saiyou_mail_appkey'] = Setting::getValueByKey('saiyou_mail_appkey');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ThrowException
     */
    public function send()
    {

        $url = 'https://api.mysubmail.com/mail/xsend';
        $content = json_encode($this->template->content);
        $result = http($url, [
            'appid' => $this->config['saiyou_mail_appid'],
            'to' => $this->to,
            'project' => $this->template->template_id,
            'signature' => $this->config['saiyou_mail_appkey'],
            'vars' => $content,
        ], 'POST');
        $status_arr = [
            "101" => "不正确的 APP ID",
            "102" => "此应用已被禁用",
            "103" => "未启用的开发者",
            "-2" => "服务器不支持",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "44" => "账号被禁用",
            "50" => "敏感词限制",
            '407' => '无效的模板ID',
        ];
        if ($result['status'] != 'success') {
            throw new ThrowException($status_arr[$result['code']]);
        }
    }
}
