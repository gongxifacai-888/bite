<?php

namespace App\Http\Middleware;

use App\DAO\SafeDAO;
use App\Models\User\User;
use Closure;

class CheckPayPassword
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $pay_password = $request->input('pay_password', '');
        if (empty($pay_password)) {
            return json_response_error(__('api.请输入支付密码'));
        }
        $user_id = User::getUserId();
        $user = User::find($user_id);
        if (!$user) {
            return json_response_error(__('api.用户不存在'));
        }
        $user_pay_password = $user->pay_password;
        if (empty($user_pay_password)) {
            return json_response_error(__('api.您未设置支付密码'), [
                'err_code' => 'NO_PAY_PASSWORD'
            ]);
        }
        if (User::encryptPassword($pay_password) != $user_pay_password) {
            return json_response_error(__('api.支付密码不正确'));
        }
        return $next($request);
    }
}
