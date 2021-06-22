<?php


namespace App\Http\Controllers\Api;

use App\Models\Setting\Setting;
use App\Models\User\Token;
use App\Models\User\User;
use App\Models\User\UserReal;
use App\Models\User\VerificationCode;
use App\Notifications\VerificationCode\BaseCode;
use App\Notify\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ThrowException;

class UserController extends Controller
{
    public function register()
    {
        return transaction(function () {
            $account      = request('account', '');
            $type        = request('type', '');
            $password    = request('password', '');
            $re_password = request('re_password', '');
            $invite_code = request('invite_code', '');
            $area_id     = request('area_id', 0);
            $sms_code    = request('sms_code', '');
            if (empty($account) || empty($type) || empty($password) || empty($sms_code)) {
                return $this->error(__('api.请填写全部内容'));
            }
            
            $user = User::where($type, $account)->first();

            if ($user) {
                throw new ThrowException(__('api.该账号已注册,请更换账号'));
            }
            
            
            if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
                throw new \Exception(__('api.密码只能在6-16位之间'));
            }
            if (!in_array($type, [User::MOBILE, User::EMAIL])) {
                throw new \Exception(__('api.非法请求'));
            }
            if ($re_password != $password) {
                throw new \Exception(__('api.两次输入的密码不一致'));
            }
//            $bool = Notify::checkCode($account, Notify::REGISTER, $sms_code);
//            if (!$bool) {
//                return $this->error(__('api.验证码不正确'));
//            }
//dd(BaseCode::TYPE_REGISTER_CODE);
            $bool = VerificationCode::checkCode($account,
                BaseCode::getCodeNotifyByType(BaseCode::TYPE_REGISTER_CODE), $sms_code);
            if (!$bool) {
                return $this->error(__('api.验证码不正确'));
            }
            $user = User::register($account, $password, $type, $invite_code, $area_id);
           // var_dump($user);die;
            $jump = Setting::getValueByKey('register_jump', '');
            return $this->success(__('api.注册成功'), [
                'user' => $user,
                'jump' => $jump,
            ]);
        });
    }

//    登录
    public function login()
    {
        return transaction(function () {
            $account    = request('account', '');
            $login_type = request('login_type', '');
            $password   = request('password', '');
            $sms_code   = request('sms_code', '');
            //如果开启了短信验证码
            if (Setting::getValueByKey('login_use_sms', 0)) {
                $bool = VerificationCode::checkCode($account,
                    BaseCode::getCodeNotifyByType(BaseCode::TYPE_LOGIN_CODE), $sms_code);
                if (!$bool) {
                    return $this->error(__('api.验证码不正确'));
                }
            }

            /**@var $user User* */
            if (!in_array($login_type, [User::EMAIL, User::MOBILE])) {
                return $this->error(__('非法请求'));
            }

            $user = User::where($login_type, $account)->first();

            if (!$user) {
                throw new ThrowException(__('api.未找到该用户,请检查后再尝试'));
            }

            $token = $user->login($password);

            return $this->success(__('api.登录成功'), $token);
        });

    }

    //注销
    public function logout()
    {
        $token = Token::getToken();
        Token::where('token', $token)->delete();
        return $this->success(__('api.注销成功'));
    }

//  修改密码
    public function amendPassword(Request $request)
    {
        $old_password       = $request->get('old_password', '');
        $new_password       = $request->get('new_password', '');
        $user               = User::getUser();
        $secondary_password = $request->get('secondary_password', '');
        if (empty($old_password) || empty($new_password) || empty($secondary_password)) {
            return $this->error(__('api.请填写完整'));
        }
        if (mb_strlen($new_password) < 6 || mb_strlen($new_password) > 16) {
            return $this->error(__('api.密码只能在6-16位之间'));
        }
        if ($user->password != User::encryptPassword($old_password)) {
            return $this->error(__('api.密码不正确'));
        }
        if ($secondary_password != $new_password) {
            return $this->error(__('api.两次密码不一致'));
        }
        try {
            $user->password = $new_password;
            $user->save();
            return $this->success(__('api.操作成功'));
        } catch (\Exception $e) {
            return $this->error(__('api.操作失败'));
        }
    }

//    忘记密码
    public function forgetPassword(Request $request)
    {
        $new_password       = $request->get('new_password', '');
        $auth_code          = request('auth_code', '');
        $account            = request('account', '');
        $type               = request('type', '');
        $secondary_password = $request->get('secondary_password', '');
        if (empty($account) || empty($new_password) || empty($secondary_password) || empty($auth_code)) {
            return $this->error(__('api.请填写完整'));
        }
        $user = User::where($type, $account)->first();
        if (empty($user)) {
            return $this->error(__('api.用户不存在'));
        }
        if (mb_strlen($new_password) < 6 || mb_strlen($new_password) > 16) {
            return $this->error(__('api.密码只能在6-16位之间'));
        }
        $bool = VerificationCode::checkCode($account,
            BaseCode::getCodeNotifyByType(BaseCode::TYPE_FORGOT_PASSWORD), $auth_code);
        if (!$bool) {
            return $this->error(__('api.验证码不正确'));
        }
        if ($secondary_password != $new_password) {
            return $this->error(__('api.两次密码不一致'));
        }
        try {
            $user->password = $new_password;
            $user->save();
            return $this->success(__('api.操作成功'));
        } catch (\Exception $e) {
            return $this->error(__('api.操作失败'));
        }
    }

//    修改支付密码
    public function amendPayPassword(Request $request)
    {
        $password  = $request->get('password', '');
        $auth_code = $request->get('auth_code', '');
        $user      = User::getUser();
        if (empty($password) || empty($auth_code)) {
            return $this->error(__('api.请填写完整'));
        }

        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error(__('api.密码只能在6-16位之间'));
        }
        $to   = $user->mobile ?: $user->email;
        $bool = VerificationCode::checkCode($to,
            BaseCode::getCodeNotifyByType(BaseCode::TYPE_CHANGE_PAY_PASSWORD_CODE), $auth_code);
        if (!$bool) {
            return $this->error(__('api.验证码错误'));
        }
        try {
            $user->pay_password = $password;
            $user->save();
            return $this->success(__('api.操作成功'));
        } catch (\Exception $e) {
            return $this->error(__('api.操作失败'));
        }
    }

    //身份认证
    public function realName(Request $request)
    {

        $user_id     = User::getUserId();
        $name        = strip_tags($request->get("name", ""));       //真实姓名
        $card_id     = strip_tags($request->get("card_id", ""));    //身份证号
        $front_pic   = strip_tags($request->get("front_pic", ""));  //正面照片
        $reverse_pic = strip_tags($request->get("reverse_pic", ""));//反面照片
        $hand_pic    = strip_tags($request->get("hand_pic", ""));   //手持身份证照片

        if (empty($name) || empty($card_id) || empty($front_pic) || empty($reverse_pic)) {
            return $this->error(__("api.请提交完整的信息"));
        }

        //校验  身份证号码合法性
        /*
        $idcheck = new IdCardIdentity();
        $res = $idcheck->check_id($card_id);
        if (!$res) {
            return $this->error(__("api.请输入合法的身份证号码"));
        }
        */
        $userreal_number = UserReal::where('card_id', $card_id)->count();
        if ($userreal_number > 0) {
            return $this->error(__("api.该身份证号已使用!"));
        }
        $user = User::find($user_id);
        if (empty($user)) {
            return $this->error(__("api.会员未找到"));
        }
        $userreal = UserReal::where('user_id', $user_id)->first();
        if (!empty($userreal)) {
            return $this->error(__("api.您已经申请过了"));
        }

        try {
            $userreal              = new UserReal();
            $userreal->user_id     = $user_id;
            $userreal->name        = $name;
            $userreal->card_id     = $card_id;
            $userreal->front_pic   = $front_pic;
            $userreal->reverse_pic = $reverse_pic;
            $userreal->hand_pic    = $hand_pic;
            $userreal->save();
            return $this->success(__('api.提交成功，等待审核'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    //个人中心  身份认证信息
    public function userCenter()
    {
        $user_id = User::getUserId();
        $user    = User::where("id", $user_id)->first();
        if (empty($user)) {
            return $this->error(__("api.会员未找到"));
        }
        $userreal   = UserReal::where('user_id', $user_id)->first();
        $arr        = [];
        $arr['id']  = $user->id;
        $arr['uid'] = $user->uid;
        if (empty($userreal)) {
            $arr['review_status'] = 0;
            $arr['name']          = '';
            $arr['card_id']       = '';
        } else {
            $arr['review_status'] = $userreal['review_status'];
            $arr['name']          = $userreal['name'];
            $arr['card_id']       = $userreal['card_id'];

        }

        if (!empty($arr['card_id'])) {
            $arr['card_id'] = mb_substr($arr['card_id'], 0, 2) . '******' . mb_substr($arr['card_id'], -2, 2);
        }
        return $this->success('', $arr);
    }

    //用户详情
    public function info()
    {
        $user = User::getUser();
        $user->load('seller');
        $user->append('is_seller');
        return $this->success('', $user);
    }

    //用户授权码获取(添加代理商是需要用)
    public function authCode()
    {
        $user_id = User::getUserId();
        if (Cache::has('authorization_code_' . $user_id)) {

            $code = Cache::get('authorization_code_' . $user_id);

        } else {
            //获取随机授权码
            $code = Str::random(6);
            //缓存
            Cache::put('authorization_code_' . $user_id, $code, 500);
        }

        return $this->success(__('api.请求成功'), $code);

    }

}
