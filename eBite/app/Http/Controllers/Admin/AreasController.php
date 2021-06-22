<?php


namespace App\Http\Controllers\Admin;

use App\Models\System\Area;
use App\Models\System\Lang;
use Illuminate\Http\Request;
use App\Models\System\Payway;

class AreasController extends Controller
{
    public function index(){
        $areas=Area::all();
        $lang=Lang::all();

        return  view('admin.areas.index',['areas'=>$areas,'langs'=>$lang]);
    }

    public function list(Request $request){
        $limit=$request->get('limit','');
        $area_id=$request->get('area_id',-1);
        $lang_id=$request->get('lang_id',-1);
        $where=[];
        if ($area_id!=-1) {
            $where[] = ['id', $area_id];
        }
        if ($lang_id!=-1) {
            $where[] = ['lang_id', $lang_id];
        }
        $list=Area::where($where)->orderBy('sort','asc')->orderBy('id','asc')->paginate($limit);

        return $this->layuiPageData($list);
    }

    public function add(Request $request){
        $id=$request->get('id','');
        $lang=Lang::all();
        if (empty($id)){
            $areas=new Area();
        }else{
            $areas=Area::where('id',$id)->first();
        }
        return view('admin.areas.edit',['areas'=>$areas,'id'=>$id,'langs'=>$lang]);
    }

    public function save(Request $request){
        $code=$request->get('code','');
        $name=$request->get('name','');
        $currency=$request->get('currency','');
        $global_code=$request->get('global_code','');
        $lang_id=$request->get('lang_id','');
        $payways=$request->get('payways','');
        $timezone=$request->get('timezone','');
        $sort=$request->get('sort','');
        $id=$request->get('id',0);
        if(!$code){
            return $this->error('请输入语言代码');
        }
        if(!$name){
            return $this->error('请输入国家名称');
        }
        if(!$currency){
            return $this->error('请输入国家货币');
        }
        if(!$global_code){
            return $this->error('请输入国家代码');
        }
        if(!$lang_id){
            return $this->error('请选择语言');
        }
        if(!$timezone){
            return $this->error('请输入时区');
        }
        if (empty($id)){
            $areas=new Area();
        }else{
            $areas=Area::where('id',$id)->first();
        }
        $areas->code=$code;
        $areas->name=$name;
        $areas->currency=$currency;
        $areas->global_code=$global_code;
        $areas->lang_id=$lang_id;
        $areas->payways=$payways;
        $areas->timezone=$timezone;
        $areas->sort=$sort;
        try{
            $areas->save();
            return $this->success('操作成功');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    public function delete(Request $request){
        $id=$request->get('id','');
        if (empty($id)){
            return $this->error('参数不存在');
        }
        try{
            Area::where('id',$id)->delete();
            return $this->success('操作成功');
        }catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
