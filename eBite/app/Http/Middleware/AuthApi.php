<?php

namespace App\Http\Middleware;

use App\Models\User\Token;
use App\Models\User\User;
use Closure;

class AuthApi
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
        //检查登陆状态
        $token = Token::getToken();
        $user_id = Token::getUserIdByToken($token);
        if (empty($user_id)) {
            return response()->json([
                'code' => '999',
                'msg' => __('请登录'),
            ]);
        }

        return $next($request)->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie');
    }
}
