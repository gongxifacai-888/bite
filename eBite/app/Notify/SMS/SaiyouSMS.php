<?php


namespace App\Notify\SMS;


use App\Models\Setting\Setting;
use App\Notify\Notify;
use App\Exceptions\ThrowException;

class SaiyouSMS extends Notify
{

    protected function config()
    {
        $this->send_type = self::SMS_DRIVER;
        $this->config['domestic_sms_appid'] = Setting::getValueByKey('domestic_sms_appid');
        $this->config['domestic_sms_appkey'] = Setting::getValueByKey('domestic_sms_appkey');
        $this->config['overseas_sms_appid'] = Setting::getValueByKey('overseas_sms_appid');
        $this->config['overseas_sms_appkey'] = Setting::getValueByKey('overseas_sms_appkey');
    }

    /**
     *
     * @param string 手机号       $mobile
     * @param string 短信签名     $content
     * @param int    国家代码     $country_code
     * @param array  参数         $params
     *
     * @throws \Exception
     */
    public function send()
    {
        $content = $this->buildVars();
        $to = $this->to;
        if ($this->area->global_code == 86) {
            $appid = $this->config['domestic_sms_appid'];
            $appkey = $this->config['domestic_sms_appkey'];
        } else {
            $to = "+{$this->area->global_code}{$to}";
            $appid = $this->config['overseas_sms_appid'];
            $appkey = $this->config['overseas_sms_appkey'];
        }
        $url = $this->getGatewayUrl($this->area->global_code);

        $result = http($url, [
            'appid' => $appid,
            'to' => $to,
            'project' => $this->template->template_id,
            'signature' => $appkey,
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

    public function buildVars()
    {
        $var = json_encode($this->template->content);
        return $var;
    }

    /**获取网关地址
     *
     * @param int $global_code
     *
     * @return string
     */
    protected function getGatewayUrl($global_code = 86)
    {
        if ($global_code == 86) {
            return 'http://api.mysubmail.com/message/xsend';
        } else {
            return 'http://api.mysubmail.com/internationalsms/xsend';
        }
    }
}
