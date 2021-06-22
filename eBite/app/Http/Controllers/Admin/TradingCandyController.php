<?php

namespace App\Http\Controllers\Admin;



use App\Models\Currency\Currency;

class TradingCandyController extends Controller
{
    //交易糖果页面
    public function index()
    {
        return view('admin.tradingCandy.index');
    }

    public function list()
    {
        return $this->layuiPageData([]);
    }

    public function add()
    {
        return view('admin.tradingCandy.add', ['currencies' => Currency::all()]);
    }

    public function save()
    {
    }

    public function edit()
    {
        return view('admin.tradingCandy.edit', ['currencies' => Currency::all()]);
    }

    public function update()
    {
    }

    public function delete()
    {
    }
}
