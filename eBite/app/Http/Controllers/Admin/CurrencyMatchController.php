<?php


namespace App\Http\Controllers\Admin;


use App\Models\Account\AccountType;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyMatch;

class CurrencyMatchController extends Controller
{
    public function index()
    {
        $quote_currencies = collect();
        $base_currencies = collect();

        foreach (Currency::cursor() as $currency) {
            if ($currency->is_quote) {
                $quote_currencies->push($currency);
            }
            $base_currencies->push($currency);
        }

        return view('admin.currencyMatch.index', [
            'quote_currencies' => $quote_currencies,
            'base_currencies' => $base_currencies,
        ]);
    }

    public function list()
    {
        $limit = request('limit', 10);
        $quote_currency_id = request('quote_currency_id', 0);
        $base_currency_id = request('base_currency_id', 0);

        $list = CurrencyMatch::with(['quoteCurrency', 'baseCurrency'])
            ->when($quote_currency_id, function ($query, $quote_currency_id) {
                $query->where('quote_currency_id', $quote_currency_id);
            })->when($base_currency_id, function ($query, $base_currency_id) {
                $query->where('base_currency_id', $base_currency_id);
            })->orderBy('sort')->paginate($limit);

        $list->each(function($currencyMatch){
            $currencyMatch->append('market_from_name');
        });

        return $this->layuiPageData($list);
    }

    public function add()
    {
        $quote_currencies = collect();
        $base_currencies = collect();

        foreach (Currency::cursor() as $currency) {
            if ($currency->is_quote) {
                $quote_currencies->push($currency);
            }
            $base_currencies->push($currency);
        }

        return view('admin.currencyMatch.add', [
            'quote_currencies' => $quote_currencies,
            'base_currencies' => $base_currencies,
        ]);
    }

    public function save()
    {
        $quote_currency_id = request('quote_currency_id', 0);
        $base_currency_id = request('base_currency_id', 0);
        $change_fee_rate = request('change_fee_rate', 0);
        $lever_fee_rate = request('lever_fee_rate', 0);
        $lever_overnight_fee_rate = request('lever_overnight_fee_rate', 0);
        $lever_point_rate = request('lever_point_rate', 0);
        $market_from = request('market_from', 0);
        $open_change = request('open_change', 0);
        $open_lever = request('open_lever', 0);
//        $open_iso = request('open_iso', 0);
        $open_microtrade = request('open_microtrade', 0);
        $lever_max_share = request('lever_max_share', 0);
        $lever_min_share = request('lever_min_share', 0);
        $lever_share_num = request('lever_share_num', 0);
        $auto_match = request('auto_match', 0);
        $match_user_id = request('match_user_id', 0);
        $sort = request('sort', 0);
        $order_risk_rate = request('order_risk_rate', 0);

        $exists = CurrencyMatch::where('quote_currency_id', $quote_currency_id)
            ->where('base_currency_id', $base_currency_id)
            ->exists();
        if ($exists) {
            return $this->error('此交易对已存在');
        }

        $currencyMatch = CurrencyMatch::create([
            'quote_currency_id' => $quote_currency_id,
            'base_currency_id' => $base_currency_id,
            'change_fee_rate' => $change_fee_rate,
            'lever_fee_rate' => $lever_fee_rate,
            'lever_overnight_fee_rate' => $lever_overnight_fee_rate,
            'lever_point_rate' => $lever_point_rate,
            'market_from' => $market_from,
            'open_change' => $open_change,
            'open_lever' => $open_lever,
//            'open_iso' => $open_iso,
            'open_microtrade' => $open_microtrade,
            'lever_max_share' => $lever_max_share,
            'lever_min_share' => $lever_min_share,
            'lever_share_num' => $lever_share_num,
            'auto_match' => $auto_match,
            'match_user_id' => $match_user_id,
            'sort' => $sort,
            'order_risk_rate' => $order_risk_rate,
        ]);

        $currencyMatch->currencyQuotation()->create([]);

        return $this->success('创建成功');
    }

    public function edit()
    {
        $id = request('id', 0);
        $quote_currencies = collect();
        $base_currencies = collect();

        foreach (Currency::cursor() as $currency) {
            if ($currency->is_quote) {
                $quote_currencies->push($currency);
            }
            $base_currencies->push($currency);
        }
        $currencyMatch = CurrencyMatch::findOrFail($id);

        return view('admin.currencyMatch.edit', [
            'quote_currencies' => $quote_currencies,
            'base_currencies' => $base_currencies,
            'currencyMatch' => $currencyMatch,
        ]);
    }

    public function update()
    {
        $id = request('id', 0);
        $quote_currency_id = request('quote_currency_id', 0);
        $base_currency_id = request('base_currency_id', 0);
        $change_fee_rate = request('change_fee_rate', 0);
        $lever_fee_rate = request('lever_fee_rate', 0);
        $lever_overnight_fee_rate = request('lever_overnight_fee_rate', 0);
        $lever_point_rate = request('lever_point_rate', 0);
        $market_from = request('market_from', 0);
        $open_change = request('open_change', 0);
        $open_lever = request('open_lever', 0);
        $open_microtrade = request('open_microtrade', 0);
        $lever_max_share = request('lever_max_share', 0);
        $lever_min_share = request('lever_min_share', 0);
        $lever_share_num = request('lever_share_num', 0);
        $auto_match = request('auto_match', 0);
        $match_user_id = request('match_user_id', 0);
        $sort = request('sort', 0);
        $order_risk_rate = request('order_risk_rate', 0);
//        $open_iso = request('open_iso', 0);

        $exists = CurrencyMatch::where('quote_currency_id', $quote_currency_id)
            ->where('base_currency_id', $base_currency_id)
            ->where('id', '<>', $id)
            ->exists();
        if ($exists) {
            return $this->error('此交易对已存在');
        }

        CurrencyMatch::findOrFail($id)->update([
            'quote_currency_id' => $quote_currency_id,
            'base_currency_id' => $base_currency_id,
            'change_fee_rate' => $change_fee_rate,
            'lever_fee_rate' => $lever_fee_rate,
            'lever_overnight_fee_rate' => $lever_overnight_fee_rate,
            'lever_point_rate' => $lever_point_rate,
            'market_from' => $market_from,
            'lever_max_share' => $lever_max_share,
            'lever_min_share' => $lever_min_share,
            'open_change' => $open_change,
            'open_lever' => $open_lever,
//            'open_iso' => $open_iso,
            'open_microtrade' => $open_microtrade,
            'lever_share_num' => $lever_share_num,
            'auto_match' => $auto_match,
            'match_user_id' => $match_user_id,
            'sort' => $sort,
            'order_risk_rate' => $order_risk_rate,
        ]);

        return $this->success('修改成功');
    }

    public function delete()
    {
        $id = request('id', 0);
        $currencyMatch = CurrencyMatch::findOrFail($id);
        $currencyMatch->currencyQuotation()->delete();
        $currencyMatch->delete();
        return $this->success('删除成功');
    }
}
