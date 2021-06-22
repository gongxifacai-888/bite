<?php

namespace App\Http\Middleware;

use Closure;

class AuthAdminHost
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
        $admin_host = config('app.admin_host');
        if ($admin_host == '*') {
            return $next($request);
        }
        if ($request->getHost() == $admin_host) {
            return $next($request);
        }
        abort(403, '非法请求!');
    }
}
