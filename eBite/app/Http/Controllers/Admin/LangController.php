<?php


namespace App\Http\Controllers\Admin;

use App\Models\System\Area;
use App\Models\System\Lang;
use Illuminate\Http\Request;

class LangController extends Controller
{
    public function index()
    {
        return view('admin.lang.index');
    }

    public function list(Request $request)
    {
        $limit = $request->get('limit', '');
        $list = Lang::orderBy('sort', 'asc')->orderBy('id', 'asc')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add(Request $request)
    {
        $id = $request->get('id', '');
        if (empty($id)) {
            $lang = new Lang();
        } else {
            $lang = Lang::where('id', $id)->first();
        }
        return view('admin.lang.edit', ['lang' => $lang, 'id' => $id]);
    }

    public function save(Request $request)
    {
        $code = $request->get('code', '');
        $name = $request->get('name', '');
        $sort = $request->get('sort', 0);
        $status = $request->get('status', 0);
        $id = $request->get('id', 0);
        if (empty($code) || empty($name)) {
            return $this->error('参数错误');
        }
        if (empty($id)) {
            $areas = new Lang();
        } else {
            $areas = Lang::where('id', $id)->first();
        }
        $areas->code = $code;
        $areas->name = $name;
        $areas->status = $status;
        $areas->sort = $sort;
        try {
            $areas->save();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function status(Request $request){
        $id = $request->get('id', '');
        $lang = Lang::find($id);
        if (empty($lang)) {
            return $this->error('参数错误');
        }
        if ($lang->status == 1) {
            $lang->status = 0;
        } elseif ($lang->status == 0) {
            $lang->status = 1;
        }
        try {
            $lang->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $id = $request->get('id', '');
        if (empty($id)) {
            return $this->error('参数不存在');
        }
        try {
            Lang::where('id', $id)->delete();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
