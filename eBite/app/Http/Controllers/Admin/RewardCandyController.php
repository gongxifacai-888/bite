<?php

namespace App\Http\Controllers\Admin;


use App\Models\Currency\Currency;

class RewardCandyController extends Controller
{
    //空投糖果页面
    public function index()
    {
        return view('admin.rewardCandy.index');
    }

    public function list()
    {
        return $this->layuiPageData([]);
    }

    public function add()
    {
        return view('admin.rewardCandy.add', ['currencies' => Currency::all()]);
    }

    public function save()
    {
    }

    public function edit()
    {
        return view('admin.rewardCandy.edit', ['currencies' => Currency::all()]);
    }

    public function update()
    {
    }

    public function delete()
    {
    }
}
