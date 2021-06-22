<?php

namespace App\Http\Middleware;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminModule;
use App\Models\Admin\AdminModuleAction;
use App\Models\Admin\OperationLog;
use App\Models\Route\AdminRoute;
use Closure;
use Illuminate\Http\Request;

/**设置管理员的访问日志
 * Class SetOperationLog
 *
 * @package App\Http\Middleware
 */
class SetOperationLog
{
    const NO_INSERT_PARAMS_ROUTES = [
        '/admin/admin/login',
        '/admin/currency/update',
        '/admin/currency/save',
        '/admin/upload/layui_upload',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (session()->has('admin_id')) {
            $admin_id = session('admin_id');
        } else {
            $username = $request->get('username', "");
            $admin = Admin::where('username', $username)->first();
            if (empty($username) || empty($admin)) {
                $admin_id = 0;
            } else {
                $admin_id = $admin->id;
            }
        }
        $arr = explode('?', $request->getRequestUri());
        if (count($arr) != 0) {
            $str=ltrim( $arr[0],'/');
            $route=AdminRoute::where('url',$str)->where('is_add_log',1)->first();

            if (!in_array($request->getRequestUri(), self::NO_INSERT_PARAMS_ROUTES)) {
                if (!empty($route)) {
                    $parameter = serialize($request->all());
                    $operation = new OperationLog();
                    $operation->ip = $request->ip();
                    $operation->admin_id = $admin_id;
                    $operation->method = $request->method();;
                    $operation->request_path = $request->getRequestUri();
                    $operation->data = $parameter;
                    $operation->name = $route->name;
                    $operation->save();
                }
            }
        }

        return $next($request);
    }
}
