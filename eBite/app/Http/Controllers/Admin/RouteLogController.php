<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/23
 * Time: 10:55
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Route\AdminRoute;

class RouteLogController extends Controller
{
    public function index()
    {
        return view('admin.route.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = AdminRoute::paginate($limit);
        return $this->layuiPageData($list);
    }

    public function addLog(Request $request)
    {
        $id = $request->get('id', 0);
        $admin_route = AdminRoute::find($id);
        if (empty($admin_route)) {
            return $this->error('参数错误');
        }
        if ($admin_route->is_add_log == 1) {
            $admin_route->is_add_log = 2;
        } else {
            $admin_route->is_add_log = 1;
        }
        try {
            $admin_route->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }




}
