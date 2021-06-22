<?php


namespace App\Http\Controllers\Admin;

use App\Models\Admin\AdminModule;
use App\Models\Admin\AdminModuleAction;
use App\Models\Admin\AdminModuleType;
use Illuminate\Support\Facades\Route;
use App\Exceptions\ThrowException;

class AdminModuleController extends Controller
{
    public function index()
    {
        return view('admin.module.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = AdminModule::paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        $moduleTypeList = AdminModuleType::get();

        $routes = Route::getRoutes();

        $route_data = [];
        foreach ($routes as $route) {
            if (substr($route->uri, 0, 5) != 'admin') {
                continue;
            }
            if (!$route->getName()) {
                continue;
            }
            $route_data[] = [
                'name' => $route->getName(),
                'uri' => $route->uri,
            ];
        }

        return view('admin.module.add', [
            'module_type_list' => $moduleTypeList,
            'route_data' => $route_data,
        ]);
    }

    public function save()
    {
        return transaction(function () {
            $name = request('name', '');
            $admin_module_type_id = request('admin_module_type_id', 0);
            $action_list = request('action', []);
            if (!$name) {
                return $this->error('请输入名称');
            }

            if (AdminModule::where('name', $name)->exists()) {
                throw new ThrowException('此名称已存在');
            }
            $module = AdminModule::create([
                'name' => $name,
                'admin_module_type_id' => $admin_module_type_id,
            ]);

            foreach ($action_list as $action) {
                $module->action()->create([
                    'action' => $action
                ]);
            }

            return $this->success('添加成功');
        });
    }

    public function edit()
    {
        $id = request('id', 0);
        $module = AdminModule::findOrFail($id);
        $moduleTypeList = AdminModuleType::get();
        $action_list = AdminModuleAction::where('admin_module_id', $id)->pluck('action')->all();

        $routes = Route::getRoutes();
        $route_data = [];
        foreach ($routes as $route) {
            if (substr($route->uri, 0, 5) != 'admin') {
                continue;
            }
            if (!$route->getName()) {
                continue;
            }

            $checked = 0;
            if (in_array($route->uri, $action_list)) {
                $checked = 1;
            }

            $route_data[] = [
                'name' => $route->getName(),
                'uri' => $route->uri,
                'checked' => $checked,
            ];
        }

        return view('admin.module.edit', [
            'module' => $module,
            'module_type_list' => $moduleTypeList,
            'route_data' => collect($route_data)->sortByDesc('checked')->values(),
        ]);
    }

    public function update()
    {
        return transaction(function () {
            $id = request('id', 0);
            $name = request('name', '');
            $admin_module_type_id = request('admin_module_type_id', 0);
            $action_list = request('action', []);
            if (!$name) {
                return $this->error('请输入名称');
            }
            if (AdminModule::where('name', $name)->where('id', '<>', $id)->exists()) {
                throw new ThrowException('此名称已存在');
            }

            $module = AdminModule::find($id);
            $module->action()->delete();

            foreach ($action_list as $action) {
                $module->action()->create([
                    'action' => $action
                ]);
            }

            $module->update([
                'name' => $name,
                'admin_module_type_id' => $admin_module_type_id,
            ]);

            return $this->success('更改成功');
        });
    }

    public function delete()
    {
        $id = request('id', 0);
        AdminModule::destroy($id);
        return $this->success('操作成功');
    }
}
