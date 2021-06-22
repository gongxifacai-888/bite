<?php

namespace App\Http\Controllers\Admin;

use App\Models\AppSetting\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function index()
    {
        return view('admin.appVersion.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $type = request('type');
        $list = AppVersion::when($type, function ($query, $type) {
            $query->where('type', $type);
        })->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        return view('admin.appVersion.add');
    }

    /**
     * @return \Illuminate\Support\Facades\Response
     * @throws \ThrowException
     */
    public function save()
    {
        return transaction(function () {
            $model = new AppVersion();
            $model->version_number = request('version_number');
            $model->wgt_url = request('wgt_url');
            $model->pkg_url = request('pkg_url');
            $model->download_url = request('download_url');
            $model->type = request('type');
            $model->save();
            return $this->success('添加成功');
        });
    }

    public function edit()
    {
        $id = request('id', 0);
        $info = AppVersion::findOrFail($id);
        return view('admin.appVersion.edit', [
            'info' => $info
        ]);
    }

    /**
     * @return \Illuminate\Support\Facades\Response
     * @throws \ThrowException
     */
    public function update()
    {
        return transaction(function () {
            $id = request('id', 0);
            $model = AppVersion::findOrFail($id);
            $model->version_number = request('version_number');
            $model->wgt_url = request('wgt_url');
            $model->pkg_url = request('pkg_url');
            $model->download_url = request('download_url');
            $model->type = request('type');
            $model->save();
            return $this->success('更改成功');
        });
    }
    public function delete()
    {
        $id = request('id', 0);
        AppVersion::destroy($id);
        return $this->success('操作成功');
    }
}
