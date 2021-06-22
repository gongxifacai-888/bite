<?php

namespace App\Http\Middleware;

use App\Models\Lever\LeverTransaction;
use App\Models\User\User;
use Closure;

class HoldCheck
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
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)->whereNotIn('status', [LeverTransaction::STATUS_CLOSED, LeverTransaction::STATUS_CANCEL])->count();
        if ($exist_close_trade > 0) {
            return json_response_error(__('api.操作失败:您有未平仓的交易,操作禁止'));
        }
        return $next($request);
    }
}
