<?php

namespace App\Jobs;

use App\Entity\TxOrder;
use App\Models\Account\AccountLog;
use App\Models\Tx\Tx;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ThrowException;

class CancelTxOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var TxOrder
     */
    protected $order;

    /**
     * @var int
     */
    protected $way;

    /**
     * CancelTxOrder constructor.
     *
     * @param TxOrder $order
     * @param int     $way
     */
    public function __construct($order, $way)
    {
        $this->order = $order;
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
            DB::transaction(function () {

                /**@var $order Tx* */
                if ($this->way == MatchEngine::IN) {
                    $order = TxIn::lockForUpdate()->find($this->order->id);
                }
                if ($this->way == MatchEngine::OUT) {
                    $order = TxOut::lockForUpdate()->find($this->order->id);
                }
                if (!$order) {
                    Log::channel('match')->error('撤单失败,撤单队列找不到单子');
                    throw new ThrowException('撤单失败,撤单队列找不到单子');
                }
                $order->returnBalance(AccountLog::TX_CANCEL)->delete();
            });
        } catch (\Throwable $t) {
            Log::channel('match')->error($t->getMessage());
            dump($t->getMessage());
            dump($t->getLine());
            dump($t->getFile());
        }
    }
}
