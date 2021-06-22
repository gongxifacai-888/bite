<?php


namespace App\Http\Controllers\Admin;

use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\TxComplete;
use App\Models\Tx\TxIn;
use App\Models\User\User;

class TxCompleteController extends Controller
{

    public function index()
    {
        $currency_matches = CurrencyMatch::get();
        return view('admin.txComplete.index',[
            'currency_matches'=>$currency_matches
        ]);
    }

    public function list()
    {
        $currency_match_id = request('currency_match_id', '');

        $list = TxComplete::with(['currencyMatch'])->when($currency_match_id, function ($query, $currency_match_id) {
            $query->where('currency_match_id', $currency_match_id);
        })->orderBy('id', 'DESC')->paginate();

        return $this->layuiPageData($list);
    }
}
