<?php

namespace App\Http\Controllers\Admin;

use App\Models\MagicForm\Field;
use App\Models\MagicForm\ModelGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModelGroupController extends Controller
{
    //
    public function index()
    {
        return view('admin.modelGroup.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $news_list = ModelGroup::orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($news_list);
    }

    public function add()
    {
        $fields = Field::all();
        return view('admin.modelGroup.add')->with('fields', $fields);
    }

    public function save(Request $request)
    {
        $name = request('name', 0);
        $field = request('field', []);

        $validator = Validator::make($request->all()
            , [
                'name' => 'required|unique:model_groups,group_name',
            ]
            , [
                "required" => ":attribute 不能为空",
                "unique" => ":attribute 已存在"
            ]
            , [
                'name' => '字段组名称',
            ]);
        $validator->after(function ($validator) {

        });
        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        ModelGroup::create([
            'group_name' => $name,
            'group_field' => $field,
        ]);

        return $this->success('操作成功');
    }

    public function edit()
    {
        $id = request('id', 0);
        $info = ModelGroup::find($id);
        return view('admin.modelGroup.edit', [
            'info' => $info,
        ]);
    }

    public function update(Request $request)
    {


        $id = request('id', 0);
        $name = request('name', 0);
        $field = request('field', []);

        $validator = Validator::make($request->all()
            , [
                'name' => [
                    'required',
                    Rule::unique('model_groups','group_name')->ignore($id)
                ],
            ]
            , [
                "required" => ":attribute 不能为空",
                "unique" => ":attribute 已存在"
            ]
            , [
                'name' => '字段组名称',
            ]);
        $validator->after(function ($validator) {

        });
        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        //$filed_json = json_encode($request->all());
        Field::findOrFail($id)->update([
            'group_name' => $name,
            'group_field' => $field,
        ]);
        return $this->success('操作成功');

    }

    public function delete()
    {
        $id = request('id', 0);
        NewsCategory::destroy($id);
        return $this->success('删除成功');
    }
}
