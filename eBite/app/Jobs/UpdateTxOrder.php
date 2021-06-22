<?php

namespace App\Jobs;

use App\Entity\TxCompleteEntity;
use App\Models\Account\AccountLog;
use App\Models\Tx\Tx;
use App\Models\Tx\TxHistory;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UpdateTxOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    protected $number;

    /**
     * @var Collection
     */
    protected $completes;

    /**
     * @var float
     */
    protected $fee;

    /**买卖方向
     * @var int
     */
    protected $way;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $completes, $fee, $way)
    {
        $this->id = $id;
        $this->completes = $completes;
        $this->fee = $fee;
        $this->way = $way;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            /**@var $order Tx* */
            //本次成交额
            $transacted_volume = 0;
            //本次成交量
            $transacted_amount = 0;
            $this->completes->each(function ($complete) use (&$transacted_amount, &$transacted_volume) {
                /**@var TxCompleteEntity $complete * */
                $transacted_amount = bc($transacted_amount, '+', $complete->amount);
                $transacted_volume = bc($transacted_volume, '+', $complete->volume);
            });

            if ($this->way == MatchEngine::IN) {
                $order = TxIn::lockForUpdate()->find($this->id);
            }
            if ($this->way == MatchEngine::OUT) {
                $order = TxOut::lockForUpdate()->find($this->id);
            }
            if (!$order) {
                Log::channel('match')->error('更新单子失败,找不到单子');
                return;
            }
            $order->update([
                'transacted_volume' => bc($order->transacted_volume, '+', $transacted_volume),
                'transacted_amount' => bc($order->transacted_amount, '+', $transacted_amount),
                'fee' => bc($order->fee, '+', $this->fee),
            ]);

            //向队列推送匹配完成单处理
            OrderMatched::dispatch($order->setAppends([])->toArray(), $this->way);

            //如果没有未成交的量则退还差价并删除
            $order_surplus_amount = bc($order->number, '-', $order->transacted_amount);
            if ($order_surplus_amount <= 0) {
                $order->returnBalance(AccountLog::TX_COMPLETE)->delete();
            }

        } catch (\Throwable $t) {
            Log::channel('match')->error($t->getMessage());
            dump($t->getMessage());
            dump($t->getLine());
            dump($t->getFile());
        }
    }
}
