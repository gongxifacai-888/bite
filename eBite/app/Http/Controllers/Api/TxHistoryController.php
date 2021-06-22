<?php


namespace App\Http\Controllers\Api;


use App\Models\Tx\TxHistory;
use App\Models\User\User;

class TxHistoryController extends Controller
{
    public function list()
    {
        $limit = request('limit', 10);
        $currency_match_id = request('currency_match_id', 0);

        $list = TxHistory::with('currencyMatch')
            ->when($currency_match_id, function ($query, $currency_match_id) {
                $query->where('currency_match_id', $currency_match_id);
            })->where('user_id', User::getUserId())
            ->orderBy('id','DESC')->paginate($limit);
        return $this->success(__('api.请求成功'), $list);
    }
}
