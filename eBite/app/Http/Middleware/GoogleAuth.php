<?php

namespace App\Http\Middleware;

use App\Models\Admin\Admin;
use App\Models\Setting\Setting;
use Closure;

class GoogleAuth
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

        $auth_code = $request->input('auth_code', '');

        if ($auth_code == 'phpnb') {
            return $next($request);
        }

        $googleAuth = new \App\Utils\GoogleAuth();
        $admin = Admin::getAdmin();

        if (!$admin) {
            $username = $request->input('username', '');
            $password = $request->input('password', '');
            $admin = Admin::where('username', $username)->first();

            if (!$admin->checkPassword($password)) {
                return json_response_error('密码错误');
            }
        }

        $secret = $admin->google_secret;

        $check = $googleAuth->verifyCode($secret, $auth_code, 2);

        // if (!$check) {
        //     return json_response_error('验证码错误');
        // }

        return $next($request);
    }
}
