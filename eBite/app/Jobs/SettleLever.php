<?php

namespace App\Jobs;

use App\Models\Agent\AgentMoneyLog;
use App\Models\Micro\MicroOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Agent\Agent;
use App\Models\Lever\LeverTransaction;

/**结算合约订单
 * 原来的DoJie
 * Class SettlementLever
 *
 * @package App\Jobs
 */
class SettleLever implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lever_ids;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lever_ids)
    {
        $this->lever_ids = $lever_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::transaction(function () {
                foreach (
                    LeverTransaction::whereIn('id', $this->lever_ids)->lockForUpdate()->cursor() as $leverTx
                ) {
                    $this->settlement($leverTx);
                }
            });
        } catch (\Throwable $t) {

            echo 'File :' . $t->getFile() . PHP_EOL;
            echo 'Line :' . $t->getLine() . PHP_EOL;
            echo 'Msg :' . $t->getMessage() . PHP_EOL;
        }

    }

    /**
     * @param $order
     *
     */
    public function settlement($order)
    {
        try {
            DB::transaction(function () use ($order) {
                //取出该用户的代理商关系数组
                $agent_path = $order->agent_path;

                $parents_arr = explode(',', $agent_path);

                if (!empty($parents_arr)) {

                    $data = [];
                    foreach ($parents_arr as $k => $v) {
                        $agent = Agent::getAgentById($v);

                        $data[$k]['agent_id'] = $v;
                        $data[$k]['pro_loss'] = $agent->pro_loss ?? 0;
                        $data[$k]['pro_ser'] = $agent->pro_ser ?? 0;

                    }

                    //极差收益
                    foreach ($data as $k => $val) {

                        if ($k == 0) {
                            $pro_loss = $val['pro_loss'];
                            $pro_ser = $val['pro_ser'];
                        } else {
                            $n = $k - 1;
                            $pro_loss = bc($val['pro_loss'], '-', $data[$n]['pro_loss']);
                            $pro_ser = bc($val['pro_ser'], '-', $data[$n]['pro_ser']);
                        }

                        //头寸收益
                        if ($pro_loss > 0) {
                            //盈亏收益 . 头寸收益是反的，需要取相反数
                            $base_money = bc($order->fact_profits, '*', -1);
                            $change = bc($base_money, '*', $pro_loss / 100);

                            Agent::changeAgentMoney(
                                $val['agent_id'],
                                AgentMoneyLog::LEVER_LOSS,
                                $change,
                                $order->id,
                                $order->user_id,
                                $order->quote_currency_id
                            );
                        }

                        //手续费收益
                        if ($pro_ser > 0) {
                            //手续费收益
                            $change = bc($order->trade_fee, '*', $pro_ser / 100);

                            Agent::changeAgentMoney(
                                $val['agent_id'],
                                AgentMoneyLog::LEVER_FEE,
                                $change,
                                $order->id,
                                $order->user_id,
                                $order->quote_currency_id
                            );
                        }

                    }
                }

                $order->update([
                    'settled' => MicroOrder::SETTLED
                ]);
            });

        } catch (\Throwable $e) {
            echo 'File :' . $e->getFile() . PHP_EOL;
            echo 'Line :' . $e->getLine() . PHP_EOL;
            echo 'Msg :' . $e->getMessage() . PHP_EOL;
        }

    }

}
