<?php


namespace App\BlockChain;

use App\Models\Account\Account;
use App\Models\Account\AccountLog;
use App\Models\Account\AccountType;
use App\Models\Chain\ChainWallet;
use App\Models\Chain\TxHash;
use App\Models\Currency\CurrencyProtocol;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ThrowException;

class EOS extends BlockChain
{

    //是否需要打入手续费
    const TRANSFER_FEE = false;

    //是否需要归拢
    const COLLECT = false;

    public $recharge_method = self::BILL_METHOD;

    protected static function api()
    {
        $parent_api = parent::api();
        $api = [
            'transfer' => '$HOST/v3/wallet/eos/sendto',
            'bills' => 'http://node.eosflare.io/v1/history/get_actions',
        ];

        return array_merge($api, $parent_api);
    }

    public function generateOnlineWallet($user, $addresses = [])
    {
        $this->generateMemoOnlineWallet($user);
    }

    public function txStatus($tx_hash)
    {

        $status = TxHash::STATUS_SUCCESS;
        return $status;
    }

    /**
     * 转账
     *
     * @param        $from
     * @param        $private_key
     * @param        $to
     * @param        $number
     * @param        $type
     * @param string $code
     *
     * @return mixed|void
     * @throws GuzzleException
     */
    public function transfer($from, $private_key, $to, $number, $type, $extra_data = [])
    {
        parent::transfer($from, $private_key, $to, $number, $type, $extra_data);
        $url = $this->getApiUrl(__FUNCTION__);
        $amount = self::convertUpper($number, $this->currencyProtocol->decimal);
        $response = http($url, [
            'fromaccount' => $from,
            'privkey' => $private_key,
            'toaccount' => $to,
            'amount' => $amount,
            'tag' => $extra_data['memo'],
            'type' => $type,
            'verificationcode' => $extra_data['code'] ?? '',
        ],'POST');
        if ($response['code'] != 0) {
            throw new ThrowException($response['msg']);
        }
        return $response['data']['txHex'];
    }

    /**账单充值
     *
     * @param BlockChain $blockChain
     */
    public function billRecharge($blockChain)
    {
        $limit = 10;
        $last_start = Cache::get('recharge:eos:last_bill_start', 1);
        $bills = $this->bills($blockChain->currencyProtocol->in_address, $last_start, $limit);
        Cache::set('recharge:eos:last_bill_start', $last_start + $limit);
        foreach ($bills as $bill) {
            try {
                DB::transaction(function () use ($bill) {
                    $amount = $bill['amount'] ?? 0;
                    if ($amount <= 0) {
                        Log::channel('wallet_recharge')->error("充值金额错误,跳过", $bill);
                        return false;
                    }
                    $memo = $bill['memo'] ?? '';
                    if (!$memo) {
                        Log::channel('wallet_recharge')->error("没有memo,跳过", $bill);
                        return false;
                    }
                    $chainWallet = ChainWallet::where('memo', $memo)->first();
                    if (!$chainWallet) {
                        Log::channel('wallet_recharge')->error("找不到钱包,跳过", $bill);
                        return false;
                    }
                    $account_type_id = AccountType::where('is_recharge', AccountType::IS_RECHARGE)->first();
                    if (!$account_type_id) {
                        Log::channel('wallet_recharge')->error("找不到账户", $bill);
                        return false;
                    }

                    $hash = $bill['tx_hash'];
                    //如果已经处理过则不处理
                    if (TxHash::where('hash', $hash)->exists()) {
                        Log::channel('wallet_recharge')->alert("处理过不处理", $bill);
                        return true;
                    }
                    $account = Account::getAccountByType($account_type_id->id, $chainWallet->currency_id,
                        $chainWallet->user_id);
                    $account->changeBalance(AccountLog::BLOCK_CHAIN_ADD, $amount);
                    TxHash::insertHash($chainWallet->user_id, $this->currency->id, TxHash::RECHARGE, $hash,
                        $this->currencyProtocol->id);
                    Log::channel('wallet_recharge')->error("充值成功EOS,UID:{$account->user()->value('uid')}", $bill);
                });
            } catch (\Throwable $t) {
                Log::channel('wallet_recharge')->error("充值发生错误", [$t->getMessage()]);
                dump($t->getMessage());
            }

        }
    }

    /**
     * 获取账单记录
     *
     * @param     $address
     * @param int $cursor
     * @param int $size
     *
     * @return array|mixed|void
     * @throws GuzzleException
     * @throws ThrowException
     */
    public function bills($address, $cursor = 0, $size = 10)
    {
        dump($cursor);
        $url = $this->getApiUrl(__FUNCTION__);
        $params = [
            'pos' => $cursor,
            'offset' => $size,
            'account_name' => $address,
        ];
        $params = json_encode($params);
        $response = raw_http($url, $params);

        $recharge_data = [];

        $actions = $response['actions'] ?? [];
        foreach ($actions as $action) {
            $action_name = $action['action_trace']['act']['name'] ?? '';
            $account = $action['action_trace']['act']['account'] ?? '';
            //如果类型不是转账不处理
            if ($action_name != 'transfer') {
                continue;
            }
            //eosio.token是eos币,如果不是则不处理
            if ($account != 'eosio.token') {
                continue;
            }
            //如果数据不正确则不处理
            $data = $action['action_trace']['act']['data'] ?? [];
            if (!$data) {
                continue;
            }
            //如果不是给总账号转的不处理
            if ($data['to'] != $address) {
                continue;
            }
            //如果交易哈希不正确不处理
            $hash = $action['action_trace']['trx_id'] ?? '';
            if (!$hash) {
                continue;
            }
            $recharge_data[] = [
                'to_address' => $data['to'],
                'memo' => $data['memo'],
                'tx_hash' => $hash,
                'amount' => explode(' ', $data['quantity'])[0],
            ];
        }
        return $recharge_data;
    }

}
