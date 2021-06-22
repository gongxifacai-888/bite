<?php


namespace App\Http\Controllers\Admin;

use App\Entity\Market\Depth;
use App\Jobs\MatchEngine;
use App\Logic\Market;
use App\Models\Currency\CurrencyMatch;
use Illuminate\Support\Facades\Artisan;

class TxController extends Controller
{
    public function index()
    {
        $currency_matches = CurrencyMatch::get();

        return view('admin.tx.index', [
            'currency_matches' => $currency_matches
        ]);
    }

    public function list()
    {
        return transaction(function () {
            $currency_match_id = request('currency_match_id', 0);
            $limit = request('limit', 999999999);

            $currencyMatch = CurrencyMatch::findOrFail($currency_match_id);

            $in = Market::depthBuys($currencyMatch->symbol, $limit);
            $out = Market::depthSells($currencyMatch->symbol, $limit);

            $depth = new Depth($currencyMatch->symbol, $in, $out);

            return $this->success('请求成功', $depth);
        });
    }

    /**将内存池清空
     *
     */
    public function delete()
    {
        Artisan::call('match:clearOrder');
        return $this->success('操作成功');
    }

    /**将内存池清空
     *
     */
    public function add()
    {
        Artisan::call('match:loadOrder');
        return $this->success('操作成功');
    }
}
