<?php

namespace App\Http\Controllers\Admin;

use App\Models\Currency\Currency;
use App\Models\Lever\LeverMultiple;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class LeverMultipleController extends Controller
{
    public function index()
    {
        return view('admin.levermultiple.index');
    }

    public function add()
    {
        return view('admin.levermultiple.add');
    }

    public function save(Request $request)
    {

        $value                 = $request->get('value', '');
        $type                  = $request->get('type', '');
        $lever_multiple        = new LeverMultiple();
        $lever_multiple->value = $value;
        $lever_multiple->type  = $type;
        try {
            $exists = LeverMultiple::where('type', $type)->where('value', $value)->exists();
            if ($exists) {
                return $this->error('参数已存在');
            }
            $lever_multiple->save();
        } catch (\Exception $ex) {
            return $this->error('参数错误');
        }
        return $this->success('添加成功');
    }

    public function list(Request $request)
    {
        $result = new LeverMultiple();
        $count  = $result::all()->count();
        $result = $result->orderBy("type", "asc")->get()->toArray();
        foreach ($result as $key => $value) {
            if ($value['type'] == 1) {
                $result[$key]['type'] = "倍数";
            } else {
                $result[$key]['type'] = "手数";
            }
        }

        return response()->json(['code' => 0, 'data' => $result, 'count' => $count]);
    }


    public function delete(Request $request)
    {


        $admin = LeverMultiple::find($request->get('id'));

        abort_unless(!is_null($admin), 404);

        $bool = $admin->delete();
        if ($bool) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    public function edit(Request $request)
    {

        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = LeverMultiple::find($id);
        //
        return view('admin.levermultiple.edit', ['result' => $result]);
    }

    //编辑用户信息
    public function update(Request $request)
    {
        $value = $request->get("value");
        $id    = $request->get("id");
        if (empty($id)) return $this->error("参数错误");
        $user        = LeverMultiple::find($id);
        $user->value = $value;
        if (empty($user)) return $this->error("数据未找到");
        try {

            $aa = $user->save();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }


    }
}
