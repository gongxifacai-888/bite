<?php


namespace App\Models\Tx;

use App\Entity\TxOrder;
use App\Logic\SocketPusher;
use App\Models\Account\AccountChange;
use App\Models\Account\AccountLog;
use App\Models\Account\ChangeAccount;
use App\Models\Currency\CurrencyMatch;
use App\Models\Model;

use App\Jobs\AsyncChangeBalance;
use App\Jobs\MatchEngine;
use App\Models\Setting\Setting;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ThrowException;

class TxOut extends Tx
{

    /**取消订单
     * 只能由 \App\Jobs\CancelTxOrder调用
     * 否则会出现数据一致性问题
     *
     * @return mixed
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
            'way' => TxHistory::OUT,
            'user_id' => $this->user_id,
        ]);
    }

    /**
     * 退还余额
     *
     * @param $log_type
     *
     * @return $this|Tx
     */
    public function returnBalance($log_type)
    {
        $balance = bc($this->number, '-', $this->transacted_amount);

        if ($balance == 0) {
            return $this;
        }
        if ($balance < 0) {
            Log::channel('match')->error("卖出退还余额失败,数量不正确:{$balance}");
            throw new ThrowException(__('model.卖出退还余额失败,数量小于0'));
        }

        $account = ChangeAccount::lockForUpdate()->find($this->base_account_id);
        if (!$account) {
            throw new ThrowException(__('model.撮合退还余额失败,找不到卖出账户'));
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
     */
    public function toMatch()
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

        if ($this->transacted_amount >= $this->number) {
            $this->returnBalance(AccountLog::TX_COMPLETE)->delete();
            return;
        }

        MatchEngine::dispatch($txOrder, MatchEngine::MATCH, self::class)
            ->onQueue("matchEngine:{$process}");
    }

    /**
     * 创建订单
     *
     * @param $user_id
     * @param $currencyMatch
     * @param $number
     * @param $price
     *
     * @return TxOut|\Illuminate\Database\Eloquent\Model|mixed
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

        $baseWallet = ChangeAccount::where('user_id', $user_id)
            ->where('currency_id', $currencyMatch->base_currency_id)->lockForUpdate()->first();

        $quoteWallet = ChangeAccount::where('user_id', $user_id)
            ->where('currency_id', $currencyMatch->quote_currency_id)->first();
        if (!$baseWallet || !$quoteWallet) {
            throw new ThrowException(__('model.找不到交易对所需钱包'));
        }
        $baseWallet->changeBalance(AccountLog::TX_CREATE, -$number);
        $baseWallet->changeLockBalance(AccountLog::TX_CREATE, $number);
        $order = self::create([
            'number' => $number,
            'price' => $price,
            'currency_match_id' => $currencyMatch->id,
            'user_id' => $user_id,
            'base_account_id' => $baseWallet->id,
            'quote_account_id' => $quoteWallet->id,
        ]);
        $order->toMatch();

        //发送通知
        $order->baseAccount->append('currency_code')->addHidden('currency');
        $order->quoteAccount->append('currency_code')->addHidden('currency');
        SocketPusher::txOrderSubmit($order->setAppends([])->toArray(),MatchEngine::OUT);

        return $order;
    }
}
