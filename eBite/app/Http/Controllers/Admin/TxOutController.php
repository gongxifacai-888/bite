<?php


namespace App\Http\Controllers\Admin;


use App\Jobs\MatchEngine;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use App\Models\User\User;

class TxOutController extends Controller
{

    public function index()
    {
        $currency_matches = CurrencyMatch::get();
        return view('admin.txOut.index', [
            'currency_matches' => $currency_matches
        ]);
    }

    public function list()
    {
        $email = request('email', '');
        $mobile = request('mobile', '');
        $uid = request('uid', '');
        $currency_match_id = request('currency_match_id', 0);

        $user = User::where('uid', $uid)->first();

        $list = TxOut::with(['currencyMatch','user'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->when($user, function ($query, $user) {
            $query->where('user_id', $user->id);
        })->when($currency_match_id, function ($query, $currency_match_id) {
            $query->where('currency_match_id', $currency_match_id);
        })->orderBy('id', 'DESC')->paginate();

        return $this->layuiPageData($list);
    }

    public function delete()
    {
        return transaction(function(){
            $id = request('id', 0);

            /**@var TxOut $order**/
            $order = TxOut::find($id);

            if(!$order){
                return $this->error('单子不存在');
            }

            $order->cancel();

            return $this->success('撤单请求已发送');
        });
    }
}
