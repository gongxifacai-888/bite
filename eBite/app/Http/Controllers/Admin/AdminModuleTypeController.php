<?php
/*
 * @Descripttion:
 * @version:
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-20 09:26:07
 */


namespace App\Http\Controllers\Admin;


use App\Models\Admin\AdminModule;
use App\Models\Admin\AdminModuleType;
use App\Exceptions\ThrowException;

class AdminModuleTypeController extends Controller
{
    public function index()
    {
        return view('admin.moduleType.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $module_type_list = AdminModuleType::paginate($limit);
        return $this->layuiPageData($module_type_list);
    }

    public function save()
    {
        return transaction(function () {
            $name = request('name', '');

            if (!$name) {
                return $this->error('请输入名称');
            }
            if (AdminModuleType::where('name', $name)->exists()) {
                return $this->error('名称已存在');
            }

            AdminModuleType::create([
                'name' => $name
            ]);
            return $this->success('更改成功');
        });
    }

    public function update()
    {
        return transaction(function () {
            $id = request('id', 0);
            $name = request('name', '');

            if (!$name) {
                return $this->error('请输入名称');
            }
            if (AdminModuleType::where('name', $name)->exists()) {
                return $this->error('名称已存在');
            }
            AdminModuleType::findOrFail($id)->update([
                'name'=>$name
            ]);
            return $this->success('更改成功');
        });
    }

    public function delete()
    {
        return transaction(function () {
            $id = request('id', 0);

            $has_module = AdminModule::where('admin_module_type_id', $id)->exists();
            if ($has_module) {
                throw new ThrowException('不能删除,该分类下还有权限');
            }
            AdminModuleType::destroy($id);
            return $this->success('删除成功');
        });
    }
}
