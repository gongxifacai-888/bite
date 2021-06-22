<?php


namespace App\Http\Controllers\Admin;


use App\Jobs\MatchEngine;
use App\Models\Account\AccountLog;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use App\Models\User\User;

class TxInController extends Controller
{

    public function index()
    {
        $currency_matches = CurrencyMatch::get();
        return view('admin.txIn.index', [
            'currency_matches' => $currency_matches
        ]);
    }

    public function list()
    {
        $uid = request('uid', '');
        $currency_match_id = request('currency_match_id', 0);
        $email = request('email', '');
        $mobile = request('mobile', '');
        $user = User::where('uid', $uid)->first();

        $list = TxIn::with(['currencyMatch','user'])
        ->when($email, function ($query, $email) {
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

            /**@var TxIn $order**/
            $order = TxIn::find($id);

            if(!$order){
                return $this->error('单子不存在');
            }

            $order->cancel();

            return $this->success('撤单请求已发送');
        });
    }

}
