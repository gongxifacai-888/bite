<?php

namespace App\Http\Middleware;

use App\Models\Agent\Agent;
use App\Models\User\User;
use App\Models\User\UserReal;
use Closure;

class RealNameAuth
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
        $user_id = User::getUserId();

        $real = UserReal::where('user_id', $user_id)->first();
        if (!$real) {
            return json_response_error(__('api.未实名认证,请先认证'));
        }
        if ($real->review_status == UserReal::REJECT) {
            return json_response_error(__('api.请等待实名认证通过'));
        }

        return $next($request);
    }
}
