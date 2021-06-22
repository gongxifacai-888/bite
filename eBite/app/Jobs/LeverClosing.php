<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Models\Account\{Account, AccountType,  AccountLog};
use App\Models\Lever\LeverTransaction;
use App\Events\Lever\LeverClosedEvent;

class LeverClosing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task_list;
    protected $deduct_balance;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task_list, $deduct_balance = true)
    {
        $this->task_list = $task_list;
        $this->deduct_balance = $deduct_balance;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lever_transactions = LeverTransaction::whereIn('id', $this->task_list)
            ->where('status', LeverTransaction::STATUS_CLOSING)
            ->get();
        foreach ($lever_transactions as $key => $trade) {
            try {
                DB::transaction(function () use ($trade) {
                    try {
                        if ($this->deduct_balance) {
                            if (!$this->deductBalance($trade)) {
                                throw new \Exception('平仓扣除资金失败');
                            }
                        }
                        //更新状态和计算最终盈利
                        $trade->status = LeverTransaction::STATUS_CLOSED;
                        $trade->fact_profits = $trade->profits;
                        $trade->complete_time = microtime(true);
                        $result = $trade->save();

                        if (!$result) {
                            throw new \Exception('平仓失败:更新平仓状态失败');
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }
                });
            } catch (\Exception $e) {
                echo 'File:' . $e->getFile() . PHP_EOL;
                echo 'Line:' . $e->getLine() . PHP_EOL;
                echo 'Message:' . $e->getMessage() . PHP_EOL;
            }
        }
        event(new LeverClosedEvent($lever_transactions));
    }

    public function deductBalance($trade)
    {
        try {
            DB::transaction(function () use ($trade) {

                $lever_wallet = Account::getAccountByType(AccountType::LEVER_ACCOUNT_ID, $trade->quote_currency_id, $trade->user_id);

                //计算盈亏
                $change = bc($trade->caution_money, '+', $trade->profits);
                //从钱包处理资金
                $pre_result = bc($lever_wallet->balance, '+', $change);
                $diff = 0;
                //是否余额不够扣除
                if (bccomp($pre_result, '0') < 0) {
                    $change = -$lever_wallet->balance;
                    $diff = $pre_result;
                }
                $extra_data = [
                    'trade_id' => $trade->id,
                    'caution_money' => $trade->caution_money,
                    'profit' => $trade->profits,
                    'diff' => $diff,
                ];
                $data = ['strict' => false, 'data' => $extra_data];
                $lever_wallet->changeBalance(AccountLog::LEVER_TRANSACTION_ADD, $change, $data);
            });
            return true;
        } catch (\Exception $e) {
            echo 'File:' . $e->getFile() . PHP_EOL;
            echo 'Line:' . $e->getLine() . PHP_EOL;
            echo 'Message:' . $e->getMessage() . PHP_EOL;
            return false;
        }
    }
}
