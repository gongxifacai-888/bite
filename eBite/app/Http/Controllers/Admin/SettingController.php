<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/22
 * Time: 11:10
 */

namespace App\Http\Controllers\Admin;

use App\Events\Setting\EditEvent;
use App\Models\Setting\Setting;
use App\Models\Setting\SettingType;

class SettingController extends Controller
{
    public function index()
    {
        $setting_types = SettingType::get();
        return view('admin.setting.index', [
            'setting_types' => $setting_types
        ]);
    }

    public function list()
    {
        $limit = request('limit', 10);
        $setting_type_id = request('setting_type_id', 0);
        $list = Setting::when($setting_type_id, function ($query) use ($setting_type_id) {
            $query->where('setting_type_id', $setting_type_id);
        })->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        $setting_types = SettingType::get();
        return view('admin.setting.add', [
            'setting_types' => $setting_types
        ]);
    }

    public function save()
    {
        $key = request('key', '');
        $value = request('value', '');
        $desc = request('desc', '');
        $setting_type_id = request('setting_type_id', '');
        $visible = request('visible', 0);
        Setting::create([
            'key' => $key,
            'value' => $value,
            'desc' => $desc,
            'setting_type_id' => $setting_type_id,
            'visible' => $visible,
        ]);

        return $this->success('操作成功');
    }

    public function edit()
    {
        $id = request('id', 0);
        $setting = Setting::find($id);
        $setting_types = SettingType::get();
        return view('admin.setting.edit', [
            'info' => $setting,
            'setting_types' => $setting_types
        ]);
    }


    public function update()
    {
        $id = request('id', 0);
        $value = request('value', '');
        $setting_type_id = request('setting_type_id', '');
        $desc = request('desc', '');
        $visible = request('visible', 0);
        $setting=Setting::where('id', $id)->firstOrFail();
        $setting->update([
            'value' => $value,
            'desc' => $desc,
            'setting_type_id' => $setting_type_id,
            'visible' => $visible,
        ]);

        event(new EditEvent($setting));

        return $this->success('操作成功');
    }

    public function delete()
    {
        $id = request('id', 0);
        Setting::destroy($id);
        return $this->success('操作成功');
    }
}
