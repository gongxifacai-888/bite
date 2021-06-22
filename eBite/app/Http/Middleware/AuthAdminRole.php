<?php

namespace App\Http\Middleware;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AuthAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $admin = Admin::getAdmin();
        $uri = Route::getCurrentRoute()->uri;

        $role = AdminRole::with('adminRoleModule.module.action:admin_module_id,action')->find($admin->role_id);

        $actions = $role->adminRoleModule->toArray();

        $actions = collect($actions)
            ->flatten(3)
            ->pluck('action')
            ->filter(function ($value) {
                return !is_null($value);
            })->values()->all();


        if (!$admin->role->is_super && !in_array($uri, $actions)) {
            if ($request->isXmlHttpRequest()) {
                return json_response_error('无权访问');
            }
            abort(403, '无权访问');
        }

        return $next($request);
    }
}
