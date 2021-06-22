<?php

namespace App\Http\Middleware;

use App\Models\Admin\Admin;
use Closure;

class AuthAdmin
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
        if (!Admin::getAdminId()) {
            if ($request->isXmlHttpRequest()) {
                return json_response_error('登陆失效,请重新登陆');
            }
            $uri = $request->getRequestUri();
            session()->put('admin_redirect_uri',$uri);
            return redirect('/admin/admin/login');
        }
        $admin = Admin::find(Admin::getAdminId());
        $ip = $request->ip();

        //如果需要单点登录就取消注释这些代码
        if ($admin->last_login_ip != $ip) {
//            session()->flush();
//            session()->regenerate();

//            if ($request->isXmlHttpRequest()) {
//                return json_response_error('登陆失效,请重新登陆');
//            }
//            return redirect('/admin/admin/login');
        }

        view()->share('admin', $admin);
        return $next($request);
    }

}
