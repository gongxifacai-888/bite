<?php


namespace App\Http\Controllers\Admin;


use App\Models\Setting\SettingType;

class SettingTypeController extends Controller
{
    public function index()
    {
        return view('admin.settingType.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = SettingType::withCount('settings')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function save()
    {
        $name = request('name');
        if(!$name){
            return $this->error('请输入名称');
        }
        if (SettingType::where('name', $name)->exists()) {
            return $this->error('这个分类已存在');
        }
        SettingType::create([
            'name' => $name
        ]);
        return $this->success('保存成功');
    }

    public function update()
    {
        try {
            $id = request('id', 0);
            $name = request('name', '');
            if(!$name){
                return $this->error('请输入名称');
            }
            SettingType::findOrFail($id)->update([
                'name' => $name
            ]);
            return $this->success('更新成功');
        } catch (\Throwable $t) {
            return $this->error($t->getMessage());
        }
    }

    public function delete()
    {
        $id = request('id', 0);
        $settingType = SettingType::find($id);
        if (!$settingType->settings()->getResults()->isEmpty()) {
            return $this->error('此分类下有设置,不能删除');
        }
        $settingType->delete();
        return $this->success('删除成功');
    }
}
