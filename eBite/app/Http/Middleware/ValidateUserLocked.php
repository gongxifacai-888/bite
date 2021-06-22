<?php

namespace App\Http\Middleware;

use App\Models\User\User;
use Closure;

class ValidateUserLocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user_id = User::getUserId();
        $user = User::find($user_id);
        if ($user) {
            if ($user->status == 1) {
                return response()->json(['code' => '0', 'msg' => '账号冻结中,不能进行此操作']);
            }
        } else {
            return response()->json(['code'=>'999','msg'=>'请登录']);
        }
        return $next($request);
    }
}
