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

/**结算秒合约订单
 *
 * Class SettleMicro
 *
 * @package App\Jobs
 */
class SettleMicro implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $micro_ids;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($micro_ids)
    {
        $this->micro_ids = $micro_ids;
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
                    MicroOrder::whereIn('id', $this->lever_ids)->lockForUpdate()->cursor() as $microOrder
                ) {
                    $this->settlement($microOrder);
                }
            });
        } catch (\Throwable $t) {

            echo 'File :' . $t->getFile() . PHP_EOL;
            echo 'Line :' . $t->getLine() . PHP_EOL;
            echo 'Msg :' . $t->getMessage() . PHP_EOL;
        }
       
    }

    /**
     * @param MicroOrder $order
     *
     * @throws \Throwable
     */
    public function settlement($order)
    {
        try {
            DB::transaction(function () use ($order) {
                /**@var MicroOrder $order * */
                //取出该用户的代理商关系数组
                $agent_path = $order->agent_path ?? '';

                $parents_arr = explode(',', $agent_path);

                if (!$parents_arr) {
                    return;
                }

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
                            AgentMoneyLog::MICRO_LOSS,
                            $change,
                            $order->id,
                            $order->user_id,
                            $order->currency_id
                        );
                    }

                    //手续费收益
                    if ($pro_ser > 0) {
                        //手续费收益
                        $change = bc($order->fee, '*', $pro_ser / 100);

                        Agent::changeAgentMoney(
                            $val['agent_id'],
                            AgentMoneyLog::MICRO_FEE,
                            $change,
                            $order->id,
                            $order->user_id,
                            $order->currency_id
                        );
                    }

                }

                $order->update([
                    'settled' => MicroOrder::SETTLED
                ]);
            });

        } catch (\Exception $e) {
            echo 'File :' . $e->getFile() . PHP_EOL;
            echo 'Line :' . $e->getLine() . PHP_EOL;
            echo 'Msg :' . $e->getMessage() . PHP_EOL;
        }

    }

    public function fee()
    {

    }
}
