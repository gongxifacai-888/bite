<?php

namespace App\Http\Middleware;

use App\Models\User\User;
use Closure;

/**检查用户是否被禁止交易
 * Class TxAuth
 *
 * @package App\Http\Middleware
 */
class TxAuth
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
        $user = User::getUser();
        if ($user->tx_status == User::LOCK) {
            return json_response_error(__('api.禁止交易'));
        }
        return $next($request);
    }
}
