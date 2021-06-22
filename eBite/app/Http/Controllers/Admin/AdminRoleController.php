<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/23
 * Time: 10:55
 */

namespace App\Http\Controllers\Admin;


use App\Models\Admin\Admin;
use App\Models\Admin\AdminModule;
use App\Models\Admin\AdminModuleAction;
use App\Models\Admin\AdminModuleType;
use App\Models\Admin\AdminRole;
use App\Models\Admin\AdminRoleModule;

class AdminRoleController extends Controller
{
    public function index()
    {
        return view('admin.role.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = AdminRole::paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        $module_type_list = AdminModuleType::all();
        return view('admin.role.add', [
            'module_type_list' => $module_type_list
        ]);
    }

    public function save()
    {
        $name = request('name', '');
        $module_id_list = request('module_id', []);
        return transaction(function () use ($name, $module_id_list) {
            $adminRole = AdminRole::create([
                'name' => $name,
            ]);
            foreach ($module_id_list as $module_id) {
                AdminRoleModule::create([
                    'admin_role_id' => $adminRole->id,
                    'module_id' => $module_id,
                ]);
            }

            return $this->success('添加成功');
        });
    }

    public function edit()
    {
        $id = request('id', 0);
        $role = AdminRole::find($id);
        $module_type_list = AdminModuleType::all();
        $role_module_list = AdminRoleModule::where('admin_role_id', $id)->pluck('module_id')->all();

        return view('admin.role.edit', [
            'role' => $role,
            'module_type_list' => $module_type_list,
            'role_module_list' => $role_module_list,
        ]);
    }

    public function update()
    {
        $id = request('id', 0);
        $name = request('name', '');
        $module_id_list = request('module_id', []);

        return transaction(function () use ($id, $name, $module_id_list) {
            AdminRole::find($id)->update([
                'name' => $name,
            ]);

            AdminRoleModule::where('admin_role_id', $id)->delete();

            foreach ($module_id_list as $module_id) {
                AdminRoleModule::create([
                    'admin_role_id' => $id,
                    'module_id' => $module_id,
                ]);
            }

            return $this->success('更改成功');
        });
    }

    public function delete()
    {
        $id = request('id', 0);

        $role = AdminRole::find($id);

        $has_admin = Admin::where('role_id', $id)->exists();

        if ($has_admin) {
            return $this->error('这个角色下面有管理员,不能删除');
        }

        if ($role->is_super) {
            return $this->error('不允许删除超级管理员角色');
        }
        $role->delete();
        return $this->success('操作成功');
    }
}
