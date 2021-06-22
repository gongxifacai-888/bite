<?php

namespace App\Http\Controllers\Admin;


use App\Models\Currency\Currency;
use App\Models\Patch\StorageCurrency;

class StorageCurrencyController extends Controller
{
    //存币宝页面
    public function index()
    {
        return view('admin.storageCurrency.index');
    }

    public function list()
    {
        $limit = request('limit', 20);
        $list  = StorageCurrency::orderBy('created_at', 'DESC')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        return view('admin.storageCurrency.add', ['currencies' => Currency::all()]);
    }

    public function save()
    {
        $data = request()->except(['id', 'file']);
        StorageCurrency::create($data);
        return $this->success('操作成功');
    }

    public function edit()
    {
        return view('admin.storageCurrency.edit', [
            'currencies' => Currency::all(),
            'info'       => StorageCurrency::findOrFail(request('id')),
        ]);
    }

    public function update()
    {
        $data = request()->except(['id', 'file']);
        StorageCurrency::findOrFail(request('id'))->update($data);
        return $this->success('操作成功');
    }

    public function delete()
    {
        StorageCurrency::destroy(request('id'));
        return $this->success('操作成功');

    }
}
