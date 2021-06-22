<?php


namespace App\Http\Controllers\Api;
use App\BlockChain\BlockChain;

use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\User\User;

class WalletController extends Controller
{
    public function wallets()
    {
        $wallets = CurrencyProtocol::with(['wallets' => function ($query) {
            $query->where('user_id', User::getUserId());
        }])->get();

        return $this->success(__('api.请求成功'), $wallets);
    }

    public function wallet()
    {
        $currency_id = request('currency_id', 0);
        $user_id = User::getUserId();
        $user = User::find($user_id);
 // var_dump($user);die;
        $currency = Currency::with(['wallets' => function ($query) {
            $query->where('user_id',User::getUserId());
            $query->select(['id','address','memo','currency_id','chain_protocol_id','currency_protocol_id']);
            $query->with(['chainProtocol'=>function($query){
                $query->with('currencyProtocols')->select('id','code');
            }]);
        }])->select(['id', 'code', 'draw_min', 'draw_max', 'open_recharge','number_code'])
            ->findOrFail($currency_id);
          $currency=json_decode(json_encode($currency),true);
             if(empty($currency['wallets'])){
             // if($currency['number_code']!=null){
                
                   $info=BlockChain::generateAllOnlineWallet($user,$currency['number_code'],$currency_id);
                     $currency = Currency::with(['wallets' => function ($query) {
                    $query->where('user_id',User::getUserId());
                    $query->select(['id','address','memo','currency_id','chain_protocol_id','currency_protocol_id']);
                    $query->with(['chainProtocol'=>function($query){
                        $query->with('currencyProtocols')->select('id','code');
                    }]);
                   }])->select(['id', 'code', 'draw_min', 'draw_max', 'open_recharge','number_code'])
                   ->findOrFail($currency_id);
              // }
          }
        // if (empty($currency['wallets']['address'])){
           
        //      $info=BlockChain::generateAllOnlineWallet($user,'520',$currency_id);
        // }
        // var_dump($info);die;
        // var_dump($currency);die;
        //        $currency_protocols = CurrencyProtocol::with(['wallets' => function ($query) {
        //            $query->where('user_id', 1);
        //        }, 'currency' => function ($query) {
        //            $query->select();
        //        }])->where('currency_id', $currency_id)->get();
        // print_r($currency);
        return $this->success(__('api.请求成功'), $currency);
    }
    
    
    
    
}
