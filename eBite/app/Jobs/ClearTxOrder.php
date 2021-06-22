<?php

namespace App\Jobs;

use App\Logic\Market;
use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**按照价格扫掉币币交易中的单子
 * Class ClearTxOrder
 *
 * @package App\Jobs
 */
class ClearTxOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**如果模型缺失则忽略任务
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * @var CurrencyMatch
     */
    protected $currencyMatch;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($currencyMatch, $price)
    {
        $this->currencyMatch = $currencyMatch;
        $this->price = $price;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->user = User::find($this->currencyMatch->match_user_id);
            if (!$this->user) {
                return;
            }
            $symbol = $this->currencyMatch->symbol;

            $in = Market::depthBuys($symbol, 9999999999);
            $out = Market::depthSells($symbol, 9999999999);

            if (rand(0, 99) > 50) {
                $this->clearInOrder($in);
                $this->clearOutOrder($out);
            } else {
                $this->clearOutOrder($out);
                $this->clearInOrder($in);
            }

            //最后吧自己的单子全部撤掉
            $this->cancelOrder();

        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getFile());
            dump($t->getLine());
        }

    }

    /**分析盘口挂买入单吃掉卖出单
     *
     */
    protected function clearOutOrder($outs)
    {
        //分析需要挂多少量
        $amount = 0;
        foreach ($outs as $out) {
            if ($out->price <= $this->price) {
                $amount = bc($out->amount, '+', $amount);
            }
        }
        if ($amount <= 0) {
            return;
        }
        dump("挂买入单量{$amount}");
        TxIn::generateTx($this->user->id, $this->currencyMatch, $amount, $this->price);
    }

    /**分析盘口挂卖出单吃掉买入单
     *
     */
    protected function clearInOrder($ins)
    {
        //分析需要挂多少量
        $amount = 0;
        foreach ($ins as $in) {
            if ($in->price >= $this->price) {
                $amount = bc($in->amount, '+', $amount);
            }
        }
        if ($amount <= 0) {
            return;
        }
        dump("挂卖出单量{$amount}");
        TxOut::generateTx($this->user->id, $this->currencyMatch, $amount, $this->price);
    }

    /**撤掉自己的单子
     *
     */
    protected function cancelOrder()
    {
        $in_orders = TxIn::where('user_id', $this->user->id)
            ->where('currency_match_id', $this->currencyMatch->id)
            ->get();

        $out_orders = TxOut::where('user_id', $this->user->id)
            ->where('currency_match_id', $this->currencyMatch->id)
            ->get();

        $out_orders->each(function ($outOrder) {
            /**@var TxOut $outOrder * */
            $outOrder->cancel();
        });
        $in_orders->each(function ($inOrder) {
            /**@var TxIn $inOrder * */
            $inOrder->cancel();
        });
    }
}
