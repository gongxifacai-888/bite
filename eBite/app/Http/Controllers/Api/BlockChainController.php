<?php

namespace App\Http\Controllers\Api;

use App\BlockChain\BlockChain;
use App\Exceptions\ThrowException;
use App\Http\Controllers\Controller;
use App\Models\Chain\ChainProtocol;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BlockChainController extends Controller
{

    /**推送充币接口
     *
     * @return Response
     * @throws ThrowException
     */
    public function apiRecharge()
    {
      return transaction(function () {
        $data = request()->all();
    file_put_contents("call_back_data.txt", "\n" . date('Y-m-d H:i:s') .$data['body'] . "\n", FILE_APPEND);

        $body = json_decode($data['body'],true);
        
     //   dd($body)
      // if ($body['tradeType'] == 1) {
      //      if($body['status'] == 3){
            BlockChain::parseApiRechargeData($body);
            return 'success';
      //      }
      //   }
      });
    }

    /**获取所有的币种协议
     *
     */
    public function currencyProtocols()
    {
        $currencies = Currency::with(['currencyProtocols' => function ($query) {
            /**@var $query Builder* */
            $query->select(['id', 'chain_protocol_id', 'currency_id', 'token_address']);
            $query->with('chainProtocol:id,code');
        }])->where('open_draw', 1)->get(['id', 'code']);

//        $currencies->each(function($currency){
//            $currencyProtocol->append('');
//        });
        return $this->success(__('api.请求成功'), $currencies);
    }

    /**获取所有的区块链 协议
     *
     */
    public function chainProtocols()
    {
        $chain_protocols = ChainProtocol::get();
        return $this->success(__('api.请求成功'), $chain_protocols);
    }
}
