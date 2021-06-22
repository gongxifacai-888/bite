<?php


namespace App\Http\Controllers\Api;


use App\Exceptions\ThrowException;
use App\Jobs\MatchEngine;
use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\Tx;
use App\Models\Tx\TxIn;
use App\Models\User\User;
use Illuminate\Validation\Rules\In;

class TxInController extends Controller
{
    /**发布
     *
     */
    public function add()
    {
        return transaction(function () {

            $currency_match_id = request('currency_match_id', 0);
            $price = request('price', 0);
            $number = request('number', 0);

            $price = parse_price($price, '', 4);
            $number = parse_number($price, $number, '', 4);

            if (!is_numeric($price) || $price <= 0) {
                return $this->error(__('api.价格错误'));
            }
            if (!is_numeric($number) || $number <= 0) {
                return $this->error(__('api.数量错误'));
            }
            // if ($number > 100000) {
            //     throw new ThrowException('挂单量不能超过100000');
            // }

            $currencyMatch = CurrencyMatch::findOrFail($currency_match_id);

            $in = TxIn::generateTx(User::getUserId(), $currencyMatch, $number, $price);

            return $this->success(__('api.发布成功'), $in);
        });
    }

    /**取消订单
     *
     * @throws \Exception
     */
    public function cancel()
    {
        $id = request('id', 0);
        $in = TxIn::find($id);
        if (!$in) {
            return $this->error(__('api.找不到这个单子'));
        }
        $in->cancel();
        return $this->success(__('api.撤单请求已发送'));
    }

    /**详情
     *
     */
    public function detail()
    {
        $id = request('id', 0);
        $in = TxIn::find($id);
        if (!$in) {
            return $this->error(__('api.找不到这个单子'));
        }
        return $this->success(__('api.请求成功'), $in);
    }

    /**列表
     *
     */
    public function list()
    {
        $limit = request('limit', 10);
        $currency_match_id = request('currency_match_id', 0);
        $ins = TxIn::with('currencyMatch')->where('user_id', User::getUserId())
            ->when($currency_match_id, function ($query, $currency_match_id) {
                $query->where('currency_match_id', $currency_match_id);
            })->orderBy('id', 'DESC')->paginate($limit);
        return $this->success(__('api.请求成功'), $ins);
    }
}
