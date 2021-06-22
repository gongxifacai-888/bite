<?php

namespace App\Http\Middleware;

use Closure;

class CheckSeller
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
        $user = \App\Models\User\User::getUser();
        if (!$user->isSeller) {
            return json_response_error(__('您不是商家无权操作'));
        }
        return $next($request);
    }
}
