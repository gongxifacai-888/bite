<?php


namespace App\Http\Controllers\Admin;

use App\Models\Currency\CurrencyMatch;
use App\Models\Currency\CurrencyQuotation as CurrencyQuotationModel;
use App\Models\Tx\HuobiSymbol;
use Illuminate\Support\Facades\Artisan;

class CurrencyQuotationController extends Controller
{
    public function index()
    {
        return view('admin.currencyQuotation.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = CurrencyQuotationModel::whereHas('currencyMatch')->with('currencyMatch')
            ->orderBy('id', 'DESC')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function clearKline()
    {
        $currency_match_id = request('currency_match_id');
        Artisan::call('market:clearKline', [
            'currency_match_id' => $currency_match_id
        ]);
        return $this->success('操作成功');
    }

    public function importKline()
    {
        $currency_match_id = request('currency_match_id');
        $symbol = request('other_symbol', null);

        $currencyMatch = CurrencyMatch::findOrFail($currency_match_id);

        if (!HuobiSymbol::where('symbol', $symbol)->exists()) {
            return $this->success('火币无此交易对');
        }

        Artisan::call('market:importKline', [
            'currency_match_id' => $currency_match_id,
            'other_symbol' => $symbol,
        ]);
        return $this->success('操作成功');
    }

}
