<?php

namespace App\Http\Controllers\Admin;

use App\Models\MagicForm\FieldProperty;
use Illuminate\Http\Request;


class FieldPropertyController extends Controller
{
    //
    public function index()
    {
        return view('admin.fieldProperty.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $element = request('element', null);

        $list = FieldProperty::where(function ($query) use ($element) {
            if($element){
                $query->where('apply_to_label',null)->orWhere('apply_to_label', $element);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        return view('admin.fieldProperty.add');
    }

    public function save(Request $request)
    {
        $data = $request->all();
        $property_name = request('property_name', 0);

        if (!$property_name) {
            return $this->error('请输入属性名称');
        }
        try {
            FieldProperty::Create(
                $data
            );
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error("操作失败：".$exception->getMessage());
        }
    }

    public function edit()
    {
        $id = request('id', 0);
        $info = FieldProperty::find($id);
        return view('admin.fieldProperty.edit', [
            'info' => $info,
        ]);
    }

    public function update(Request $request)
    {

        return transaction(function() use ($request){

            $id = request('id', 0);
            $property_name = request('property_name', 0);
            $data = $request->except('id');

            if (!$property_name) {
                return $this->error('请输入属性名称');
            }

            FieldProperty::findOrFail($id)->update($data);

            return $this->success('操作成功');
        });

    }

    public function delete()
    {
        $id = request('id', 0);
        FieldProperty::destroy($id);
        return $this->success('删除成功');
    }
}
