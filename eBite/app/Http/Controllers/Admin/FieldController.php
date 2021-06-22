<?php

namespace App\Http\Controllers\Admin;

use App\Models\MagicForm\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FieldController extends Controller
{
    //

    public function index()
    {
        return view('admin.field.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $news_list = Field::orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($news_list);
    }

    public function add()
    {
        $elements = Field::getAllElements();
        return view('admin.field.add')->with('elements', $elements);
    }

    public function save(Request $request)
    {
        $name = $request->get('name', '');

        $validator = Validator::make($request->all()
            , [
                'name' => 'required|unique:fields,field_name',
                'element' => 'required',
            ]
            , [
                "required" => ":attribute 不能为空",
                "unique" => ":attribute 已存在"
            ]
            , [
                'name' => '字段名称',
                'element' => '字段标签',
            ]);
        $validator->after(function ($validator) {

        });
        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        //$filed_json = json_encode($request->all());
        Field::create([
            'field_name' => $name,
            'field_object' => $request->all(),
        ]);

        return $this->success('操作成功');
    }

    public function edit()
    {
        $id = request('id', 0);
        $info = Field::find($id);
        $elements = Field::getAllElements();
        return view('admin.field.edit', [
            'info' => $info,
        ])->with(['elements' => $elements]);
    }

    public function update(Request $request)
    {

        $id = request('id', 0);
        $name = request('name', '');
        $validator = Validator::make($request->all()
            , [
                'name' => [
                    'required',
                    Rule::unique('fields','field_name')->ignore($id)
                ],
                'element' => 'required',
            ]
            , [
                "required" => ":attribute 不能为空",
                "unique" => ":attribute 已存在"
            ]
            , [
                'name' => '字段名称',
                'element' => '字段标签',
            ]);
        $validator->after(function ($validator) {

        });
        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        Field::findOrFail($id)->update([
            'field_name' => $name,
            'field_object' => $request->all(),
        ]);

        return $this->success('操作成功');

    }

    public function delete()
    {
        $id = request('id', 0);
        Field::destroy($id);
        return $this->success('删除成功');
    }
}
