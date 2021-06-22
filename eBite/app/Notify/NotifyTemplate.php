<?php


namespace App\Notify;


use App\Models\User\NotifyLog;
use App\Notify\Notify;
use App\Exceptions\ThrowException;

abstract class NotifyTemplate
{
    /**
     * @var []
     */
    public $content = [];

    /**是否是验证码
     *
     * @var bool
     */
    public $is_code = false;

    /**模板id
     *
     * @var mixed|string
     */
    public $template_id = '';

    /**
     * @var Notify
     */
    public $notify;

    /**
     * NotifyTemplate constructor.
     *
     * @param       $notify
     * @param array $content
     *
     * @throws \Exception
     */
    public function __construct($notify, $content = [])
    {
        $this->notify = $notify;
        $this->setContent($content);
        $this->config();
        //如果模板是短信的,则直接设置好验证码内容
        if ($this->is_code) {
//            $this->checkResend($this->notify->to, $this->notify->scene);
            $this->setContent(['code' => $this->getCode()]);
        }
    }

    public function setContent($content)
    {
        $this->content = array_merge($this->content, $content);
    }

    /**模板初始化时载入配置
     *
     *
     */
    protected abstract function config();


    /**
     * 检查某个手机号在某个场景下是否可以重新发送验证码
     *
     * @param string $to
     * @param array  $scene
     *
     * @throws \Exception
     */
    public function checkResend($to, $scene)
    {
        $notify = NotifyLog::where('to', $to)->where('type', $scene[0])
            ->where('created_at', '>', now()->subSeconds(Notify::RESEND))->exists();

        if ($notify) {
            throw new ThrowException(__('notify.发送验证码限制:seconds秒', [
                'seconds' => Notify::RESEND
            ]));
        }

    }



    public static function strReplace($template_str, $param)
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


    /**获取一个验证码
     *
     * @param int $length
     *
     * @return int
     */
    public static function getCode($length = 4)
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }
}
