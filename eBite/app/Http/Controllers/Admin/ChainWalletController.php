<?php


namespace App\Http\Controllers\Admin;

use App\BlockChain\BlockChain;
use App\Models\Account\AccountLog;
use App\Models\Chain\ChainWallet;
use App\Models\Chain\TxHash;
use App\Models\Currency\CurrencyProtocol;
use App\Models\User\User;
use App\Models\Currency\Currency;
use App\Models\Account\Account;
use Illuminate\Http\Request;

class ChainWalletController extends Controller
{
    public function index()
    {
        $currency_protocols = CurrencyProtocol::all();
        return view('admin.chainWallet.index', [
            'currency_protocols' => $currency_protocols
        ]);
    }

    public function list(Request $request)
    {
        $uid = $request->get('uid', '');
        $limit = $request->get('limit', 20);
        $email = request('email', '');
        $mobile = request('mobile', '');
        $currency_protocol_id = $request->get('currency_protocol_id', 0);

        $wallets = ChainWallet::with(['user', 'currency', 'chainProtocol'])
            ->when($currency_protocol_id, function ($query, $currency_protocol_id) {
                $query->where('currency_protocol_id', $currency_protocol_id);
            })->when($uid, function ($query, $uid) {
                $query->whereHas('user', function ($query) use ($uid) {
                    $query->where('uid', $uid);
                });
            })->when($email, function ($query, $email) {
                $query->whereHas('user', function ($query) use ($email) {
                    $query->where('email', $email);
                });
            })->when($mobile, function ($query, $mobile) {
                $query->whereHas('user', function ($query) use ($mobile) {
                    $query->where('mobile', $mobile);
                });
            })->orderBy('balance', 'DESC')->paginate($limit);

        $wallets->getCollection()->each(function ($wallet) {
            $wallet->append('currency_code', 'uid', 'chain_protocol_code');
        });

        return $this->layuiPageData($wallets);
    }

    /**
     * 转手续费
     */
    public function transferFee()
    {
        return transaction(function () {
            $id = request('id');
            $wallet = ChainWallet::with(['currency', 'currencyProtocol'])->findOrFail($id);
            $wallet->transferFee();
            return $this->success('转入手续费成功');
        });
    }

    /**归拢
     *
     * @return \Illuminate\Support\Facades\Response
     * @throws \Throwable
     */
    public function collect()
    {
        return transaction(function () {
            $wallet_id = request('id');
            $wallet = ChainWallet::with(['currency', 'currencyProtocol'])->findOrFail($wallet_id);
            $wallet->collect();
            return $this->success('发起归拢成功');
        });
    }

    /**
     * 刷新链上余额
     */
    public function refreshOnlineBalance()
    {
        return transaction(function () {
            $id = request('id');
            $wallet = ChainWallet::findOrFail($id);
            $wallet->refreshOnlineBalance();

            return $this->success('刷新成功');
        });
    }

}
