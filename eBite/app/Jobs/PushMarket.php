<?php

namespace App\Jobs;

use App\Entity\Market\Depth;
use App\Entity\TxCompleteEntity;
use App\Events\Market\DepthEvent;
use App\Events\Market\KlineEvent;
use App\Logic\Market;
use App\Logic\SocketPusher;
use App\Models\Currency\CurrencyMatch;
use App\Models\Currency\CurrencyQuotation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

/**推送行情
 * Class PushMarket
 *
 * @package App\Jobs
 */
class PushMarket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $currency_match_id;

    /**
     * @var int
     */
    protected $symbol;

    /**
     * @var float
     */
    protected $close;

    /**
     * @var Collection
     */
    protected $completes;

    /**成交量
     *
     * @var float
     */
    protected $volume = 0;

    /**成交额
     *
     * @var float
     */
    protected $amount = 0;

    /**深度
     *
     * @var Depth
     *
     */
    protected $depth = null;

    /**行情来自哪里
     *
     * @var int
     *
     */
    protected $market_from = CurrencyMatch::EXCHANGE;


    /**
     *
     * @param int        $currency_match_id 交易对id
     * @param int        $symbol            交易对
     * @param float      $close             收盘价
     * @param Collection $completes         交易完成记录
     * @param Depth      $depth             深度
     * @param int        $market_from       深度
     */
    public function __construct(
        $currency_match_id, $symbol, $close, $completes, $depth = null, $market_from = CurrencyMatch::EXCHANGE
    )
    {
        $this->currency_match_id = $currency_match_id;
        $this->symbol = $symbol;
        $this->close = $close;
        $this->completes = $completes;
        $this->depth = $depth;
        $this->market_from = $market_from;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->close) {
            //推送市场概要
            $this->pushDayMarket();
            //推送k线
            $this->pushKline();
            //推送全站交易
            $this->pushGlobalTx();
        }
        //推送深度
        $this->pushDepth();
        //写入到数据库
        $this->writeMarketToDB();

    }

    /**推送交易概要
     *
     */
    public function pushDayMarket()
    {
        $timestamp = now()->timestamp;
        $this->completes = $this->completes->transform(function ($complete) use ($timestamp) {
            /**@var TxCompleteEntity $complete * */
            $this->volume += $complete->volume;
            $this->amount += $complete->amount;
            $complete->timestamp = $timestamp;
            return $complete;
        });

//        $quotation->updateQuotation($this->close, $this->volume, $this->amount);

//        SocketPusher::dayMarket($this->symbol, $quotation, $this->currency_match_id);
    }

    /**推送全站交易
     *
     * @throws \Exception
     */
    public function pushGlobalTx()
    {
        //推送全站交易
        if($this->completes->isEmpty()){
            return;
        }
        SocketPusher::globalTx($this->symbol, $this->completes, $this->currency_match_id);
    }

    /**推送k线
     *
     */
    public function pushKline()
    {
        Market::handleKline($this->currency_match_id, $this->symbol, $this->close, $this->amount, $this->market_from);
    }

    /**推送深度
     *
     */
    public function pushDepth()
    {
        if (!$this->depth) {
            $this->depth = new Depth($this->symbol, null, null, $this->currency_match_id);
            $this->depth->in = Market::depthBuys($this->symbol);
            $this->depth->out = Market::depthSells($this->symbol);
        }
        event(new DepthEvent($this->depth, $this->market_from));
    }

    /**写入到数据库
     *
     */
    public function writeMarketToDB()
    {

    }
}
