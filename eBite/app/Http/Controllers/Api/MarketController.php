<?php


namespace App\Http\Controllers\Api;


use App\Entity\Market\Depth;
use App\Logic\Market;
use App\Logic\Robot;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyMatch;
use App\Models\Currency\CurrencyQuotation;
use App\Models\Setting\Setting;
use App\Models\Tx\TxComplete;
use App\Models\Account\Account;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,authorization,Authorization");
class MarketController extends Controller
{
    /**行情
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quotation()
    {
        $id = request('id', '');
        $order_by = request('order_by', 'change');
        $direction = request('direction', 'DESC');

        $quotations = CurrencyQuotation::whereHas('currencyMatch')
            ->when($id, function ($query, $id) {
                $query->where('id', $id);
            })->orderBy($order_by, $direction)->get();

        return $this->success(__('api.请求成功'), $quotations);
    }

    /**k线
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function kline()
    {
        
        $currency_match_id = request('currency_match_id', 0);
        $period = request('period', '1min');
        $size = request('size', Market::KLINE_SIZE);

        $currencyMatch = CurrencyMatch::find($currency_match_id);
        if (!$currencyMatch) {
            return $this->error(__('api.交易对不存在'));
        }
        if (!in_array($period, Market::PERIODS)) {
            return $this->error(__('api.错误的period'));
        }

        $klines = Market::getKline($currencyMatch->symbol, $period, $size);

        return $this->success(__('api.请求成功'), $klines);
    }

    /**交易界面行情汇总
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deal()
    {
        $currency_match_id = request('currency_match_id', 0);
        $depth_limit = request('depth_limit', 15);
        $tx_complete_limit = request('tx_complete_limit', 15);

        $currencyMatch = CurrencyMatch::find($currency_match_id);
        if (!$currencyMatch) {
            return $this->error(__('api.交易对不存在'));
        }

        $depth = new Depth($currencyMatch->symbol, null, null, $currency_match_id);
        //如果行情不是来自交易所,盘口直接返回空值,等待socket推送假行情即可,否则会因为网络延迟,先显示真实挂单,然后突然变成假行情
        if ($currencyMatch->market_from == CurrencyMatch::EXCHANGE) {
            $buys = Market::depthBuys($currencyMatch->symbol, $depth_limit);
            $sells = Market::depthSells($currencyMatch->symbol, $depth_limit);
            $depth = new Depth($currencyMatch->symbol, $buys, $sells, $currency_match_id);
        }

        $tx_completes = TxComplete::where('currency_match_id')->limit($tx_complete_limit)->get();

        $quotation = CurrencyQuotation::with('currencyMatch.quoteCurrency')
            ->where('currency_match_id', $currency_match_id)->first()
            ->append('cny_price');

        $cny_usd = Setting::getValueByKey('cny_usd', '7');

        return $this->success(__('api.请求成功'), [
            'depth' => $depth,
            'tx_completes' => $tx_completes,
            'quotation' => $quotation,
            'cny_usd' => $cny_usd,
        ]);
    }

    /**请求系统内的所有交易对
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function currencyMatches()
    {
        $currencyMatches = Currency::with(['matches'=>function($query){
            $query->with('currencyQuotation')->orderBy('sort');
        }])->where('is_quote', Currency::ON)->get();
        return $this->success(__('api.请求成功'), $currencyMatches);
    }

}
