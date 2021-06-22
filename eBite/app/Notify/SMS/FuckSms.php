<?php


namespace App\Notify\SMS;


use App\Exceptions\ThrowException;
use App\Models\Setting\Setting;
use App\Notify\Notify;
use App\Notify\NotifyTemplate;

class FuckSms extends Notify
{
    protected function config()
    {
        $this->send_type = self::FUCK_DRIVER;
        $this->config['fuck_sms_uid'] = Setting::getValueByKey('fuck_sms_uid', '0968919810');
        $this->config['fuck_sms_pwd'] = Setting::getValueByKey('fuck_sms_pwd', 'qwer1234');
    }

    /**
     * @param $to
     * @param $content
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\Exception
     */
    public function send()
    {
        $smsHost = "http://api.every8d.com/API21/HTTP/sendSMS.ashx";
        http($smsHost, [
            'UID'  => $this->config['fuck_sms_uid'],
            'PWD'  => $this->config['fuck_sms_pwd'],
            'SB'   => 'Verification Code',
            'MSG'  => "Your Verification Code is " . $this->template->content['code'],
            'DEST' => '+' . $this->area->global_code . $this->to,
        ]);
    }
}
