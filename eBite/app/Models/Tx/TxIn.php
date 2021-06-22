<?php


namespace App\Models\Tx;

use App\Entity\TxOrder;
use App\Logic\SocketPusher;
use App\Models\Account\Account;
use App\Models\Account\AccountChange;
use App\Models\Account\AccountChangeLog;
use App\Models\Account\AccountLog;
use App\Models\Account\ChangeAccount;
use App\Models\Currency\CurrencyMatch;
use App\Models\Model;

use App\Jobs\AsyncChangeBalance;
use App\Jobs\MatchEngine;
use App\Models\Setting\Setting;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ThrowException;

class TxIn extends Tx
{

    /**取消订单
     * 只能由 \App\Jobs\CancelTxOrder调用
     * 否则会出现数据一致性问题
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function cancel()
    {
        $process = $this->currencyMatch->use_process;

        $txOrder = new TxOrder();

        $txOrder->id = $this->id;
        $txOrder->symbol = $this->currencyMatch->symbol;
        $txOrder->currency_match_id = $this->currencyMatch->id;
        $txOrder->price = $this->price;
        $txOrder->user_id = $this->user_id;
        $txOrder->market_from = $this->currencyMatch->market_from;
        $txOrder->change_fee_rate = $this->currencyMatch->change_fee_rate;
        $txOrder->base_account_id = $this->base_account_id;
        $txOrder->quote_account_id = $this->quote_account_id;
        $txOrder->amount = bc($this->number, '-', $this->transacted_amount);

        MatchEngine::dispatch($txOrder, MatchEngine::CANCEL, self::class)
            ->onQueue("matchEngine:{$process}");
    }

    /**
     * @return TxHistory|bool|\Illuminate\Database\Eloquent\Model|null
     * @throws \Exception
     */
    public function delete()
    {
        parent::delete();
        return $this->createHistory();
    }

    /**创建历史完成记录
     *
     * @return TxHistory|\Illuminate\Database\Eloquent\Model
     */
    public function createHistory()
    {
        return TxHistory::create([
            'currency_match_id' => $this->currency_match_id,
            'price' => $this->price,
            'number' => $this->number,
            'transacted_volume' => $this->transacted_volume,
            'transacted_amount' => $this->transacted_amount,
            'fee' => $this->fee,
            'way' => TxHistory::IN,
            'user_id' => $this->user_id,
        ]);
    }

    /**退还余额
     *
     * @return $this
     * @throws ThrowException
     */
    public function returnBalance($log_type)
    {
        //最大成交额
        $max_volume = bc($this->number, '*', $this->price);
        $balance = bc($max_volume, '-', $this->transacted_volume);

        if ($balance == 0) {
            return $this;
        }
        if ($balance < 0) {
            Log::channel('match')->error("买入退还余额失败,数量不正确:{$balance}");
            throw new ThrowException(__('model.买入退还余额失败,数量小于0'));
        }

        $account = ChangeAccount::lockForUpdate()->find($this->quote_account_id);
        if (!$account) {
            throw new ThrowException(__('model.撮合退还余额失败,找不到买入账户'));
        }
        //扣冻结
        $account->changeLockBalance($log_type, -$balance);
        //加余额
        $account->changeBalance($log_type, $balance);

        return $this;
    }

    /**推送到撮合引擎
     *
     * @return mixed
     * @throws \Exception
     */
    public function toMatch()
    {
        $this->load('currencyMatch');
        $process = $this->currencyMatch->use_process;

        $txOrder = new TxOrder();

        $txOrder->id = $this->id;
        $txOrder->symbol = $this->currencyMatch->symbol;
        $txOrder->currency_match_id = $this->currencyMatch->id;
        $txOrder->price = $this->price;
        $txOrder->user_id = $this->user_id;
        $txOrder->market_from = $this->currencyMatch->market_from;
        $txOrder->change_fee_rate = $this->currencyMatch->change_fee_rate;
        $txOrder->base_account_id = $this->base_account_id;
        $txOrder->quote_account_id = $this->quote_account_id;
        $txOrder->amount = bc($this->number, '-', $this->transacted_amount);

        if ($this->transacted_amount >= $this->number) {
            $this->returnBalance(AccountLog::TX_COMPLETE)->delete();
            return;
        }

        MatchEngine::dispatch($txOrder, MatchEngine::MATCH, self::class)
            ->onQueue("matchEngine:{$process}");
    }

    /**
     * 生成订单
     *
     * @param $user_id
     * @param $currencyMatch
     * @param $number
     * @param $price
     *
     * @return TxIn|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Exception
     */
    public static function generateTx($user_id, $currencyMatch, $number, $price)
    {
        //强行将四位小数后的数字截掉
        $number = bc($number, '+', 0, 4);
        $price = bc($price, '+', 0, 4);

        $match_on = Setting::getValueByKey('match_on', 0);
        if (!$match_on) {
            throw new ThrowException(__('model.撮合交易已关闭'));
        }
        if ($currencyMatch->open_change == CurrencyMatch::OFF) {
            throw new ThrowException(__('model.此交易对未开启撮合交易'));
        }
        if ($number <= 0) {
            throw new ThrowException(__('model.数量设置错误'));
        }
        if ($price <= 0) {
            throw new ThrowException(__('model.价格设置错误'));
        }

        /**@var Account $quoteWallet * */
        $quoteWallet = ChangeAccount::where('user_id', $user_id)
            ->where('currency_id', $currencyMatch->quote_currency_id)->lockForUpdate()->first();

        /**@var Account $baseWallet * */
        $baseWallet = ChangeAccount::where('user_id', $user_id)
            ->where('currency_id', $currencyMatch->base_currency_id)->first();

        if (!$quoteWallet || !$baseWallet) {
            throw new ThrowException(__('model.找不到交易对所需钱包'));
        }

        $balance = bc($number, '*', $price);

        $quoteWallet->changeBalance(AccountLog::TX_CREATE, -$balance);
        $quoteWallet->changeLockBalance(AccountLog::TX_CREATE, $balance);

        $order = self::create([
            'number' => $number,
            'price' => $price,
            'currency_match_id' => $currencyMatch->id,
            'user_id' => $user_id,
            'quote_account_id' => $quoteWallet->id,
            'base_account_id' => $baseWallet->id,
        ]);
        $order->toMatch();

        $order_array = $order->setAppends([])->toArray();
        $baseWallet->addHidden('currency');
        $quoteWallet->addHidden('currency');
        $order_array['base_account'] = $baseWallet->setAppends(['currency_code'])->toArray();
        $order_array['quote_account'] = $quoteWallet->setAppends(['currency_code'])->toArray();
        SocketPusher::txOrderSubmit($order_array, MatchEngine::IN);

        return $order;
    }
}
