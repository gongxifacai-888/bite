<?php

namespace App\Http\Controllers\Admin;

use App\Models\MagicForm\FormModel;
use Illuminate\Http\Request;

class FormModelController extends Controller
{
    //
    public function index()
    {
        return view('admin.formModel.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $news_list = FormModel::orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($news_list);
    }

    public function add()
    {
        return view('admin.formModel.add');
    }

    public function save(Request $request)
    {
        $data = $request->all();
        try {
            FormModel::Create(
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
        $info = FormModel::find($id);
        return view('admin.formModel.edit', [
            'info' => $info,
        ]);
    }

    public function update()
    {

        return transaction(function(){

            $id = request('id', 0);
            $form_name = request('form_name', 0);

            if (!$form_name) {
                return $this->error('请输入模型名称');
            }

            FormModel::findOrFail($id)->update([
                'form_name' => $form_name,
            ]);

            return $this->success('操作成功');
        });
    }

    public function delete()
    {
        $id = request('id', 0);
        FormModel::destroy($id);
        return $this->success('删除成功');
    }
}
