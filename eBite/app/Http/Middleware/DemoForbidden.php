<?php

namespace App\Http\Middleware;

use App\Exceptions\ThrowException;
use Closure;

class DemoForbidden
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
        throw_if(config('app.is_demo'), new ThrowException('demo程序不允许此操作', 403));
        return $next($request);
    }
}
