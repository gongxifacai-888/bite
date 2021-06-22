<?php


namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Admin;
use App\Models\Otc\{OtcDetail, OtcMaster, Seller, SellerApply, SellerCurrency, SellerCurrencyApply,OtcAction};
use App\Models\User\User;

class OtcController extends Controller
{

    public function seller()
    {
        return view('admin.otc.seller');
    }

    public function sellerList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $status = $request->input('status', -1);
        $uid = $request->input('uid', 0);

        $email = request('email', '');
        $mobile = request('mobile', '');
        $seller_name = $request->input('seller_name', '');
        /** @var \Illuminate\Pagination\LengthAwarePaginator $sellers */
        $sellers = Seller::when($status >= 0, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($uid != 0, function ($query) use ($uid) {
            $query->whereHas('user', function ($query) use ($uid) {
                $query->where('uid', $uid);
            });
        })->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->when($seller_name != '', function ($query) use ($seller_name) {
             $query->where('name', 'like', '%' .$seller_name.'%');

        })->orderBy('id', 'desc')->paginate($limit);
        $items = $sellers->getCollection();
        $items->transform(function ($item, $key) {
            $item->append(['mobile', 'email','uid']);
            return $item;
        });
        return $this->layuiPageData($sellers);
    }

    public function sellerEdit(Request $request)
    {
        $id = $request->input('id', 0);
        $users = User::where('status', 1)->when($id <= 0, function ($query) {
                $query->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('sellers')
                        ->whereRaw('sellers.user_id = users.id');
                });
            })->get();
        $seller = Seller::findOrNew($id);
        return view('admin.otc.sellerEdit', [
            'seller' => $seller,
            'users' => $users,
        ]);
    }

    public function postSellerEdit(Request $request)
    {
        try {
            $id = $request->input('id', 0);
            $user_id = $request->input('user_id', 0);
            $name = $request->input('name', '');
            $seller = Seller::findOrNew($id);
            User::findOrFail($user_id);
            $validator = Validator::make($request->input(), [
                'name' => 'required|string|min:1',
                'user_id' => 'required|integer|min:1',
            ], [], [
                'name' => '商家名称',
                'user_id' => '用户',
            ]);
            throw_if($validator->fails(), new \Exception($validator->errors()->first()));
            // 防止用户被重复添加成为商家
            throw_if(
                Seller::where('user_id', $user_id)->where('id', '<>', $id)->exists(),
                new \Exception("该用户已是商家,不能重复被添加!")
            );
            // 商家名称不能重复
            throw_if(
                Seller::where('name', $name)->where('id', '<>', $id)->exists(),
                new \Exception("商家名称重复,不能添加!")
            );
            if ($seller->exists) {
                $data = $request->except('id', 'user_id');
            } else {
                $data = $request->except('id');
            }
            $seller->fill($data)->save();
            return $this->success(__('ok'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return $this->error("{$ex->getModel()}模型实例找不到");
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function sellerCurrency()
    {
        $currencies = SellerCurrency::getLegalCurrencies();
        return view('admin.otc.sellerCurrency', [
            'currencies' => $currencies,
        ]);
    }

    public function sellerCurrencyList(Request $request)
    {
        $seller_id = $request->input('seller_id', 0);
        $currency_id = $request->input('currency_id', 0);
        $limit = $request->input('limit', 20);
        $seller_currencies = SellerCurrency::when($seller_id > 0, function ($query) use ($seller_id) {
                $query->where('seller_id', $seller_id);
            })->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })->orderBy('id','desc')->paginate($limit);
        return $this->layuiPageData($seller_currencies);
    }

    public function sellerCurrencyEdit(Request $request)
    {
        $id = $request->input('id', 0);
        $sellers = Seller::get();
        $currencies = SellerCurrency::getLegalCurrencies();
        $seller_currency = SellerCurrency::findOrNew($id);
        return view('admin.otc.sellerCurrencyEdit', [
            'sellers' => $sellers,
            'currencies' => $currencies,
            'seller_currency' => $seller_currency,
        ]);
    }

    public function postSellerCurrencyEdit(Request $request)
    {
        try {
            $id = $request->input('id', 0);
            $seller_id = $request->input('seller_id', 0);
            $currency_id = $request->input('currency_id', 0);
            $seller_currency = SellerCurrency::findOrNew($id);
            // 检测币种是否已存在
            throw_if(
                SellerCurrency::where('seller_id', $seller_id)
                    ->where('currency_id', $currency_id)
                    ->where('id', '<>', $id)
                    ->exists(),
                new \Exception(__("商家对应币种已存在,不能重复添加"))
            );
            if (!$seller_currency->exists) {
                $data = $request->except('id');
            } else {
                throw_if($currency_id != $seller_currency->currency_id, new \Exception(__('币种不能更改')));
                $data = $request->except('id', 'currency_id'); // 币种id不可更改
            }
            $seller_currency->fill($data)->save();
            return $this->success(__('ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function master()
    {
        $currencies = SellerCurrency::getLegalCurrencies();
        return view('admin.otc.master', ['currencies' => $currencies]);
    }

    public function masterList(Request $request)
    {
        $uid = $request->get('uid', 0);
        $seller_name = $request->get('seller_name', '');
        $currency_id = $request->get('currency_id', -1);
        $status = $request->get('status', -1);
        $limit = $request->get('limit', 20);
        $way = $request->get('way', '');
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $email = request('email', '');
        $mobile = request('mobile', '');
        $list = OtcMaster::when($status >= 0, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($currency_id > 0, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->when($way != '', function ($query) use ($way) {
            $query->where('way', $way);
        })->when($uid != 0, function ($query) use ($uid) {
            $query->whereHas('user', function ($query) use ($uid) {
                $query->where('uid', $uid);
            });
        })->when($seller_name != '', function ($query) use ($seller_name) {
            $query->whereHas('seller', function ($query) use ($seller_name) {
                $query->where('name', 'like','%'.$seller_name.'%');
            });
        })->when($start_time != '', function ($query) use ($start_time) {
            $query->where('created_at','>=', $start_time);
        })->when($end_time != '', function ($query) use ($end_time) {
            $query->where('created_at','<=', $end_time);
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function masterEdit(Request $request)
    {
        $master_id = $request->input('master_id', 0);
        $otc_master = OtcMaster::find($master_id);
        return view('admin.otc.masterEdit', [
            'otc_master' => $otc_master,
        ]);
    }

    public function postMasterEdit(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $admin = Admin::find(Admin::getAdminId());
                $master_id = $request->input('master_id', 0);
                $operate_status = $request->input('operate_status', 0);
                $otc_master = OtcMaster::lockForUpdate()->find($master_id);
                $status_map = [
                    OtcMaster::STATUS_PAUSED => 'pauseMaster',
                    OtcMaster::STATUS_OPENED => 'startMaster',
                    OtcMaster::STATUS_STOPPED => 'stopMaster',
                    OtcMaster::STATUS_FINISHED => 'finishMaster',
                    OtcMaster::STATUS_CANCELED => 'cancelMaster',
                ];
                throw_if(!in_array($operate_status, array_keys($status_map)), new \Exception('操作状态非法'));
                $method = $status_map[$operate_status];
                throw_if(in_array($otc_master->status, [
                    OtcMaster::STATUS_FINISHED,
                    OtcMaster::STATUS_CANCELED,
                ]), new \Exception('当前挂单状态不允许操作'));
                throw_if($otc_master->status == $operate_status, new \Exception('当前挂单状态请勿重复操作'));
                call_user_func([OtcMaster::class, $method], $otc_master, $admin, OtcMaster::OPERATOR_ADMIN);
            });
            return $this->success('ok');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function detail()
    {
        $currencies = SellerCurrency::getLegalCurrencies();
        return view('admin.otc.detail', ['currencies' => $currencies]);
    }

    public function detailList(Request $request)
    {
        $status = $request->get('status', -1);
        $limit = $request->get('limit', 20);
        $way = $request->get('way', '');
        $buy_account = $request->get('buy_account', '');
        $sell_account = $request->get('sell_account', '');
        $master_id = $request->get('master_id', 0);
        $currency_id = $request->get('currency_id', -1);
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $buy_mobile = $request->get('buy_mobile', '');
        $sell_mobile = $request->get('sell_mobile', '');
        $buy_email = $request->get('buy_email', '');
        $sell_email = $request->get('sell_email', '');
        $list = OtcDetail::when($status != -1, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($currency_id > 0, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->when($way != '', function ($query) use ($way) {
            $query->where('way', $way);
        })->when($master_id != 0, function ($query) use ($master_id) {
            $query->where('master_id', $master_id);
        })->when($buy_account != '', function ($query) use ($buy_account) {
            $query->whereHas('buyUser', function ($query) use ($buy_account) {
                $query->where('uid', $buy_account);
            });
        })->when($sell_account != '', function ($query) use ($sell_account) {
            $query->whereHas('sellUser', function ($query) use ($sell_account) {
                $query->where('uid', $sell_account);
            });
        })->when($buy_mobile != '', function ($query) use ($buy_mobile) {
            $query->whereHas('sellUser', function ($query) use ($buy_mobile) {
                $query->where('mobile', $buy_mobile);
            });
        })->when($sell_mobile != '', function ($query) use ($sell_mobile) {
            $query->whereHas('buyUser', function ($query) use ($sell_mobile) {
                $query->where('mobile', $sell_mobile);
            });
        })->when($buy_email != '', function ($query) use ($buy_email) {
            $query->whereHas('sellUser', function ($query) use ($buy_email) {
                $query->where('uid', $buy_email);
            });
        })->when($sell_email != '', function ($query) use ($sell_email) {
            $query->whereHas('sellUser', function ($query) use ($sell_email) {
                $query->where('uid', $sell_email);
            });
        })->when($start_time != '', function ($query) use ($start_time) {
            $query->where('created_at','>=', $start_time);
        })->when($end_time != '', function ($query) use ($end_time) {
            $query->where('created_at','<=', $end_time);
        })->orderBy('id', 'desc')->paginate($limit);

        $list->getCollection()->each(function($item){
            $item->setAppends(['currency_name','buy_user_account','sell_user_account']);
        });

        return $this->layuiPageData($list);
    }

    public function detailEdit(Request $request)
    {
        $detail_id = $request->input('detail_id', 0);
        $otc_detail = OtcDetail::find($detail_id);
        return view('admin.otc.detailEdit', [
            'otc_detail' => $otc_detail,
        ]);
    }

    public function postDetailEdit(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $admin = Admin::find(Admin::getAdminId());
                $detail_id = $request->input('detail_id', 0);
                $operate_status = $request->input('operate_status', 0);
                $otc_detail = OtcDetail::lockForUpdate()->find($detail_id);
                $status_map = [
                    OtcDetail::STATUS_CANCELED => 'cancel',
                    OtcDetail::STATUS_CONFIRMED => 'confirm',
                ];
                throw_if(!in_array($operate_status, array_keys($status_map)), new \Exception('操作状态非法'));
                $method = $status_map[$operate_status];
                throw_if($otc_detail->status != OtcDetail::STATUS_ARBITRATED, new \Exception('当前交易状态不允许后台操作'));
                throw_if($otc_detail->status == $operate_status, new \Exception('当前挂单状态请勿重复操作'));
                call_user_func([OtcDetail::class, $method], $otc_detail, $admin, OtcMaster::OPERATOR_ADMIN);
            });
            return $this->success('ok');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    //商家申请
    public function sellerApply()
    {
        return view('admin.otc.sellerApply');
    }

    public function sellerApplyList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $status = $request->input('status', -1);
        $uid = $request->input('uid', 0);
        $email = request('email', '');
        $mobile = request('mobile', '');
        $applies = SellerApply::when($status >= 0, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($uid != 0, function ($query) use ($uid) {
            $query->whereHas('user', function ($query) use ($uid) {
                $query->where('uid', $uid);
            });
        })->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->orderBy('id', 'desc')->paginate($limit);
        $items = $applies->getCollection();
        $items->transform(function ($item, $key) {
            $item->append(['mobile', 'email','uid']);
            return $item;
        });
        return $this->layuiPageData($applies);
    }

    public function sellerApplyOperation(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {

                $id = $request->input('id', 0);
                $operate = intval($request->input('operate', 0));
                $apply = SellerApply::lockForUpdate()->findOrFail($id);
                if($apply->status!= SellerApply::STATUS_SUBMIT){
                    throw new \Exception('操作状态非法');
                }
                if(!in_array($operate,[1,2])){
                    throw new \Exception('参数错误');
                }
                $apply->update(['status'=>$operate]);

                if($operate == 1){
                    //添加商家
                    $seller=Seller::create([
                        'user_id'=>$apply->user_id,
                        'name'=>$apply->name,
                        'status'=>1
                        ]);
                    //添加相应币种
                    $currencies=explode(',',$apply->currencies);
                    if(count($currencies)>0){
                        foreach($currencies as $v){
                           $data=[
                               'seller_id'=>$seller->id,
                               'currency_id'=>$v
                           ];
                          SellerCurrency::create($data);

                        }

                    }

                 }

            });
            return $this->success('ok');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
     //商家币种权限审核
     public function sellerCurrencyApply()
     {
         return view('admin.otc.sellerCurrencyApply');
     }

     public function sellerCurrencyApplyList(Request $request)
     {
         $limit = $request->input('limit', 10);
         $status = $request->input('status', -1);
         $uid = $request->input('uid',0);
         $email = request('email', '');
         $mobile = request('mobile', '');
         $seller_name = $request->input('seller_name', '');
         $applies = SellerCurrencyApply::when($status >= 0, function ($query) use ($status) {
             $query->where('status', $status);
         })->when($uid != 0, function ($query) use ($uid) {
             $query->whereHas('user', function ($query) use ($uid) {
                 $query->where('uid',$uid);
             });
         })->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->when($seller_name != '', function ($query) use ($seller_name) {
            $query->whereHas('seller', function ($query) use ($seller_name) {
                $query->where('name', 'like', '%' .$seller_name.'%');
            });
        })->orderBy('id', 'desc')->paginate($limit);

         return $this->layuiPageData($applies);
     }

     public function sellerCurrencyApplyOperation(Request $request)
     {
         try {
             DB::transaction(function () use ($request) {

                 $id = $request->input('id', 0);
                 $operate = intval($request->input('operate', 0));
                 $apply = SellerCurrencyApply::lockForUpdate()->findOrFail($id);
                 if($apply->status!= SellerCurrencyApply::STATUS_SUBMIT){
                     throw new \Exception('操作状态非法');
                 }
                 if(!in_array($operate,[1,2])){
                     throw new \Exception('参数错误');
                 }
                 $apply->update(['status'=>$operate]);
                 //添加相应币种
                 if($operate == 1){
                    $currencies=explode(',',$apply->currencies);
                    if(count($currencies)>0){
                        foreach($currencies as $v){
                           $data=[
                               'seller_id'=>$apply->seller_id,
                               'currency_id'=>$v
                           ];
                          SellerCurrency::create($data);

                        }

                    }

                 }


             });
             return $this->success('ok');
         } catch (\Throwable $th) {
             return $this->error($th->getMessage());
         }
     }

     //交易追踪信息
     public function actions()
    {
        return view('admin.otc.actions');
    }

    public function actionList(Request $request)
    {
        $detail_id = $request->input('detail_id', 0);
        $status = $request->input('status', -1);
        $limit = $request->input('limit', 20);
        $actions = OtcAction::when($detail_id > 0, function ($query) use ($detail_id) {
                $query->where('detail_id', $detail_id);
            })->when($status > 0, function ($query) use ($status) {
                $query->where('status', $status);
            })->orderBy('id','desc')->paginate($limit);
        $items = $actions->getCollection();
        $items->transform(function ($item, $key) {
            $item->append(['user_account', 'sell_user_account','buy_user_account']);
            return $item;
        });
        return $this->layuiPageData($actions);
    }

}
