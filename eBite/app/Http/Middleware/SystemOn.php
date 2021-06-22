<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\Setting\Setting;
use Closure;
use Illuminate\Support\Facades\Cache;

class SystemOn
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
        $on = Setting::getValueByKey('system_on', 1);
        if ($on) {
            return $next($request);
        } else {
            if ($request->isXmlHttpRequest()) {
                return json_response_error('系统正在维护');
            } else {
                abort(403, '系统正在维护');
            }
        }
    }
}
