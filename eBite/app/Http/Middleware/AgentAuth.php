<?php

namespace App\Http\Middleware;

use App\Models\Agent\Agent;
use Closure;

class AgentAuth
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
        $agent_id = session()->get('agent_id');

        if (empty($agent_id)) {
            $uri = $request->getRequestUri();
            session()->put('agent_redirect_uri',$uri);
            return redirect('/agent');
        }
        $agent = Agent::where('id', $agent_id)->first();

        if (!$agent || $agent['is_lock'] == 1) {
            return redirect('/agent');
        }
        return $next($request);
    }
}
