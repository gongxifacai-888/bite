<?php

namespace App\Http\Controllers\Api;

use App\Events\Otc\SellerApplyEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\Otc\{SellerApplyRequest, SellerCurrencyApplyRequest};
use App\Models\Otc\{SellerApply, SellerCurrencyApply};
use App\Models\User\User;

class SellerController extends Controller
{
    public function applySeller(SellerApplyRequest $request)
    {
        try {
            $user = User::getUser();
            $data = $request->validationData();
            if($data){
                foreach($data as $k=>$v){
                    if($k=='currencies'){
                        $currencies=implode(',',$v);
                        unset($data[$k]);
                    }
                
                }
            }
            $data = array_merge($data, [
                'user_id' => $user->id,
                'status' => 0,
                'currencies'=>$currencies
            ]);
            $seller_apply = DB::transaction(function () use ($data) {
                $seller_apply = SellerApply::create($data);
                event(new SellerApplyEvent($seller_apply));
                return $seller_apply;
            });
            
            return $this->success(__('api.申请成功'), $seller_apply);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function applySellerCurrency(SellerCurrencyApplyRequest $request)
    {
        try {
            $user = User::getUser();
            $data = $request->validationData();
            $currencies=implode(',',$data['currencies']);
           
            $data =[
                'user_id' => $user->id,
                'seller_id' => $user->seller->id,
                'status' => 0,
                'currencies'=>$currencies
            ];
            $seller_currency_apply = DB::transaction(function () use ($data) {
                $seller_currency_apply = SellerCurrencyApply::create($data);
                
                return $seller_currency_apply;
            });
            
            return $this->success(__('api.申请成功'), $seller_currency_apply);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }

        
    }
}
