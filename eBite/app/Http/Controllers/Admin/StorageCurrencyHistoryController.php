<?php

namespace App\Http\Controllers\Admin;


use App\Models\Currency\Currency;
use App\Models\Patch\StorageCurrencyHistory;

class StorageCurrencyHistoryController extends Controller
{
    //村币宝页面
    public function index()
    {
        return view('admin.storageCurrencyHistory.index', ['currencies' => Currency::all()]);
    }

    public function list()
    {
        $limit = request('15');
        $list = StorageCurrencyHistory::with(['user', 'storageCurrency'])->orderByDesc('created_at')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        return view('admin.storageCurrencyHistory.add', ['currencies' => Currency::all()]);
    }

    public function save()
    {
    }

    public function edit()
    {
        return view('admin.storageCurrencyHistory.edit', ['currencies' => Currency::all()]);
    }

    public function update()
    {
    }

    public function delete()
    {
    }
}
