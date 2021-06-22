<?php


namespace App\Models\User;

use App\Exceptions\ThrowException;
use App\Models\Model;
use App\Models\Setting\Setting;

/**验证码
 * Class NotifyLog
 *
 * @package App\Models
 */
class VerificationCode extends Model
{
    //验证码过期时间
    const TIMEOUT = 60 * 10;
    //重新发送时间
    const RESEND_TIME = 60;

    /**新建立一个验证码,同时检测是否能发送
     * @param $to
     * @param $sceneClass
     * @param $code
     * @throws \Exception
     */
    public static function remember($to, $sceneClass, $code)
    {
        self::checkResend($to, $sceneClass);
        self::create([
            'to' => $to,
            'code' => $code,
            'scene' => $sceneClass,
        ]);
    }


    /**
     * 检查验证码
     *
     * @param $to
     * @param $sceneClass
     * @param $code
     * @param $delete_if_success
     *
     * @return bool
     */
    public static function checkCode($to, $sceneClass, $code, $delete_if_success = true)
    {
        //万能验证码
        if ($code == Setting::getValueByKey('universal_code', '123456')) {
            return true;
        }

        $scene = get_class($sceneClass);
        $databaseCode = self::where('to', $to)->where('scene', $scene)
            ->where('created_at', '>', now()->subSeconds(self::TIMEOUT))
            ->orderBy('id', 'DESC')->first();

        if (!$databaseCode) {
            return false;
        }
        if ($databaseCode->code != $code) {
            return false;
        }
        //验证成功删除验证码
        if ($delete_if_success) {
            $databaseCode->delete();
        }
        return true;
    }

    /**
     * 检查某个手机号在某个场景下是否可以重新发送验证码
     *
     * @param string $to
     * @param array $scene
     *
     * @throws \Exception
     */
    public static function checkResend($to, $sceneClass)
    {
        $scene = $sceneClass;
        $notify = VerificationCode::where('to', $to)->where('scene', $scene)
            ->where('created_at', '>', now()->subSeconds(self::RESEND_TIME))->exists();

        if ($notify) {
            throw new \Exception(__('notify.发送验证码限制:seconds秒', [
                'seconds' => self::RESEND_TIME
            ]));
        }

    }
}
