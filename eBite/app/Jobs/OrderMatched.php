<?php

namespace App\Jobs;

use App\Logic\SocketPusher;
use App\Models\Account\ChangeAccount;
use App\Models\Tx\Tx;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**撮合订单已匹配后
 * Class OrderMatched
 *
 * @package App\Jobs
 */
class OrderMatched implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $order;

    /**本次交易手续费
     *
     * @var float
     */
    protected $fee;

    /**买入单还是卖出单
     *
     * @var int
     */
    protected $way;

    /**
     * Create a new job instance.
     *
     * @return void
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
            //给用户推送更新单子和刷新余额

            $baseAccount = ChangeAccount::find($this->order['base_account_id']);
            $baseAccount->addHidden('currency');
            $this->order['base_account'] = $baseAccount->setAppends(['currency_code'])->toArray();

            $quoteAccount = ChangeAccount::find($this->order['quote_account_id']);
            $quoteAccount->addHidden('currency');
            $this->order['quote_account'] = $quoteAccount->setAppends(['currency_code'])->toArray();

            SocketPusher::txOrderMatched($this->order, MatchEngine::WAY_NAME[$this->way]);

        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getFile());
            dump($t->getLine());
        }

    }

}
