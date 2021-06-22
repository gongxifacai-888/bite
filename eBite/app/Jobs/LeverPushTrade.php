<?php

namespace App\Jobs;

use App\Logic\SocketPusher;
use App\Models\Account\LeverAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Currency\CurrencyMatch;
use App\Models\Lever\LeverTransaction;

class LeverPushTrade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $currencyMatch;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $currency_match)
    {
        $this->userId = $user_id;
        $this->currencyMatch = $currency_match;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            echo "user_id:" . $this->userId . PHP_EOL;
            $currency_match =  $this->currencyMatch;
            //推送用户风险率和订单
            $user_account = LeverAccount::where('user_id', $this->userId)
                ->where('currency_id', $currency_match->quote_currency_id)
                ->first();
            if (!$user_account) {
                throw new \Exception('钱包不存在');
            }
            //取盈亏总额
            list(
                'caution_money_total' => $caution_money_all,
                'origin_caution_money_total' => $origin_caution_money_all,
                'profits_total' => $profits_all
            ) = LeverTransaction::getUserProfit($this->userId, $currency_match->quote_currency_id);
            //取该交易对盈亏总额
            list(
                'caution_money_total' => $caution_money,
                'origin_caution_money_total' => $origin_caution_money,
                'profits_total' => $profits
            ) = LeverTransaction::getUserProfit($this->userId, $currency_match->quote_currency_id, $currency_match->base_currency_id);
            //取风险率
            $hazard_rate = LeverTransaction::getAccountHazardRate($user_account);

            //取用户所有持仓交易
            $lever_transaction_all = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
                ->where('currency_match_id', $currency_match->id)
                ->orderBy('id', 'desc')
                ->get();
            //委托中交易
            $lever_transaction_entrust = LeverTransaction::where('status', LeverTransaction::STATUS_ENTRUST)
                ->where('currency_match_id', $currency_match->id)
                ->orderBy('id', 'desc')
                ->get();
            //取交易对持仓交易
            $lever_transaction_cur = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
                ->where('currency_match_id', $currency_match->id)
                ->where('user_id', $this->userId)
                ->orderBy('id', 'desc')
                ->get();
            $push_data = [
                'type' => 'lever_trade',
                'to' => $this->userId,
                'currency_match_id' => $currency_match->id,
                'currency_match_name' => $currency_match->lower_symbol,
                'currency_match_id',
                'hazard_rate' => $hazard_rate, //风险率
                'caution_money_all' => $caution_money_all, //总保证金
                'origin_caution_money_all' => $origin_caution_money_all, //原始总保证金
                'profits_all' => $profits_all, //总盈亏
                'caution_money' => $caution_money, //当前交易对的保证金
                'origin_caution_money' => $origin_caution_money, //当前交易对的原始保证金
                'profits' => $profits, //当前交易对的盈亏金额
                'trades_all' => $lever_transaction_all, //所有交易
                'trades_entrust' => $lever_transaction_entrust, //取所有委托交易
                'trades_cur' => $lever_transaction_cur, //当前交易对的交易
            ];
            SocketPusher::leverTrade($push_data, $this->userId);

        } catch (\Exception $e) {
            echo '文件:' . $e->getFile() . PHP_EOL;
            echo '行号:' . $e->getLine() . PHP_EOL;
            echo '错误:' . $e->getMessage() . PHP_EOL;
            return ;
        }
    }
}
