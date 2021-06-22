<?php


namespace App\Http\Controllers\Api;

use App\Exceptions\ThrowException;
use App\Models\System\Area;
use App\Models\User\User;
use App\Models\User\VerificationCode;
use App\Notifications\VerificationCode\BaseCode;
use App\Notify\Notify;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Notification;

class NotifyController extends Controller
{
    /**发送短信验证码
     *
     * @throws ThrowException
     */
    public function sendSmsCode()
    {
        return transaction(function () {
            $to      = request('to', '');
            $area_id = request('area_id', '');
            $scene   = request('type', '');
            if (empty($area_id) || empty($scene) || empty($to)) {
                return $this->error(__('api.参数错误'));
            }
            $area = Area::find($area_id);

            if (!$area) {
                return $this->error(__('api.国家或地区不存在'));
            }

            $user = new User([
                'mobile'  => $to,
                'area_id' => $area_id,
            ]);
            Notification::send($user, BaseCode::getCodeNotifyByType($scene));

            return $this->success(__('api.发送验证码成功'));
        });
    }

    /**发送邮箱验证码
     *
     * @throws ThrowException
     */
    public function sendEmailCode()
    {
        return transaction(function () {
            $to      = request('to', '');
            $area_id = request('area_id', '');
            $scene   = request('type', '');

            $area = Area::find($area_id);
            if (!$area) {
                return $this->error(__('api.国家或地区不存在'));
            }
            if (empty($area_id) || empty($scene) || empty($to)) {
                return $this->error(__('api.参数错误'));
            }

            $user = new User([
                'email'   => $to,
                'area_id' => $area_id,
            ]);
             
            $info =Notification::send($user, BaseCode::getCodeNotifyByType($scene));
          //  dd($info);
            return $this->success(__('api.发送验证码成功'));
        });

    }

    /**验证密码
     *
     */
    public function checkCode()
    {
        $to = request('to', '');
        $scene = request('type', '');
        $code = request('code', '');
        $bool = VerificationCode::checkCode($to, BaseCode::getCodeNotifyByType($scene), $code, false);
        if ($bool) {
            return $this->success(__('api.验证码成功'));
        } else {
            return $this->error(__('api.验证码错误'));
        }
    }
}
