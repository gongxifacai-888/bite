<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class LimitRequest
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $account      = request('account', '');
        $last_request = Cache::store('redis')->has("{$account}");
        if ($last_request) {
            return json_response_error(__('api.歇一会再点吧'));
        }
        Cache::store('redis')->put("{$account}", '996', now()->addSecondS(10));
        return $next($request);
    }
}
