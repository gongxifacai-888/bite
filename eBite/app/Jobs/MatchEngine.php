<?php

namespace App\Jobs;

use App\Entity\TxCompleteEntity;
use App\Entity\TxOrder;
use App\Models\Account\ChangeAccountLog;
use App\Models\Account\AccountLog;
use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\Tx;
use App\Models\Tx\TxComplete;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ThrowException;

/**撮合引擎
 * Class MatchEngine
 *
 * @package App\Jobs
 */
class MatchEngine implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //挂单
    const MATCH = 1;

    //撤单
    const CANCEL = 2;

    //order是买入
    const IN = 1;

    //order是卖出
    const OUT = 2;

    //买入卖出的字符串值
    const WAY_NAME = [
        self::IN  => 'in',
        self::OUT => 'out',
    ];

    /**
     * @var TxOrder
     */
    protected $order;

    /**注意!,此属性如果是买入,那么订单也必须是买入单
     *
     * @var int
     *
     */
    protected $way;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $symbol;

    /**
     * MatchEngine constructor.
     *
     * @param array $order 订单
     * @param int   $type  业务类型:[挂单,撤单]
     * @param Tx    $class 类
     */
    public function __construct($order, $type, $class)
    {
        $this->order = $order;
        $this->type  = $type;
        $this->init($class);
    }

    /**初始化
     *
     */
    public function init($class)
    {
        if ($class == TxIn::class) {
            $this->way = self::IN;
        }

        if ($class == TxOut::class) {
            $this->way = self::OUT;
        }

        $this->symbol = $this->order->symbol;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            //撤单
            if ($this->type == self::CANCEL) {
                $this->cancel();
            }
            //撮合
            if ($this->type == self::MATCH) {
                $this->match();
            }
        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getLine());
            dump($t->getFile());
        }
    }

    /**执行撮合
     *
     * @throws Exception
     */
    public function match()
    {
        if ($this->way == self::IN) {
            $this->matchInOrder();
        }
        if ($this->way == self::OUT) {
            $this->matchOutOrder();
        }
    }

    /**执行买入撮合
     *
     * @throws Exception
     */
    public function matchInOrder()
    {
        $out_orders = $this->getOrders($this->symbol, self::OUT);
        //收盘价
        $price = 0;
        //交易完成记录
        $completes = collect();
        //需要移除的id
        $remove_ids = collect();

        $out_orders = $out_orders->toArray();

        $in_fee = 0;
        foreach ($out_orders as $id => &$out_order) {
            /**@var TxOrder $out_order * */
            if ($this->order->amount <= 0) {
                break;
            }
            if (bc($out_order->price, '>', $this->order->price)) {
                break;
            }

            $price     = $out_order->price;
            $tx_amount = $this->getAmount($out_order, $this->order);

            if ($out_order->amount <= 0) {
                $remove_ids->push($id);
            }
            //加减金额
            $fees = $this->changeBalance($this->order, $out_order, $tx_amount, $price);
            //加上去买入手续费
            $in_fee = bc($fees['in_fee'], '+', $in_fee);

            $txCompleteEntity                    = new TxCompleteEntity();
            $txCompleteEntity->in_user_id        = $this->order->user_id;
            $txCompleteEntity->out_user_id       = $out_order->user_id;
            $txCompleteEntity->price             = $price;
            $txCompleteEntity->amount            = $tx_amount;
            $txCompleteEntity->way               = TxComplete::IN;
            $txCompleteEntity->currency_match_id = $this->order->currency_match_id;
            $completes->prepend($txCompleteEntity);

            //更新一下卖出单
            $this->updateOrder($id, collect([$txCompleteEntity]), $fees['out_fee'], self::OUT);
        }

        $out_orders = collect($out_orders);
        //移除匹配完成的订单
        $remove_ids->each(function ($id) use (&$out_orders) {
            $out_orders->pull($id);
        });

        if ($price) {
            //计算交易量
            $transacted_number = 0;
            $completes->each(function ($complete) use (&$transacted_number) {
                /**@var TxCompleteEntity $complete * */
                $transacted_number = bc($transacted_number, '+', $complete->amount);
            });
            //有价格代表单子被撮合过,更新一下
            $this->updateOrder($this->order->id, $completes, $in_fee, self::IN);
            //将匹配过后的订单重新放到列表内
            $this->setOrders($this->symbol, self::OUT, $out_orders);
        }
        //吧当前订单放到订单列表内
        $this->pushOrder();
        //推送行情
        $this->pushMarket($price, $completes);
    }


    /**执行卖出撮合
     *
     * @throws Exception
     */
    public function matchOutOrder()
    {
        $in_orders = $this->getOrders($this->symbol, self::IN);

        //收盘价
        $price = 0;
        //交易成功
        $completes = collect();
        //需要移除的id
        $remove_ids = collect();
        //卖出手续费
        $out_fee = 0;

        $in_orders = $in_orders->toArray();
        foreach ($in_orders as $id => &$in_order) {
            /**@var TxOrder $in_order * */
            if ($this->order->amount <= 0) {
                break;
            }
            if (bc($in_order->price, '<', $this->order->price)) {
                break;
            }

            $price  = $in_order->price;
            $amount = $this->getAmount($in_order, $this->order);

            if ($in_order->amount <= 0) {
                $remove_ids->push($id);
            }

            //加减金额
            $fees = $this->changeBalance($in_order, $this->order, $amount, $price);
            //加上去卖出手续费
            $out_fee = bc($fees['out_fee'], '+', $out_fee);

            $txCompleteEntity                    = new TxCompleteEntity();
            $txCompleteEntity->in_user_id        = $in_order->user_id;
            $txCompleteEntity->out_user_id       = $this->order->user_id;
            $txCompleteEntity->price             = $price;
            $txCompleteEntity->amount            = $amount;
            $txCompleteEntity->way               = TxComplete::OUT;
            $txCompleteEntity->currency_match_id = $this->order->currency_match_id;
            $completes->prepend($txCompleteEntity);

            //更新一下卖出单
            $this->updateOrder($id, collect([$txCompleteEntity]), $fees['in_fee'], self::IN);
        }

        $in_orders = collect($in_orders);
        $remove_ids->each(function ($id) use (&$in_orders) {
            $in_orders->pull($id);
        });

        if ($price) {
            $completes->each(function ($complete) use (&$transacted_number) {
                /**@var TxCompleteEntity $complete * */
                $transacted_number = bc($transacted_number, '+', $complete->amount);
            });

            //有价格代表单子被撮合过,更新一下
            $this->updateOrder($this->order->id, $completes, $out_fee, self::OUT);
            //将匹配过后的订单重新放到列表内
            $this->setOrders($this->symbol, self::IN, $in_orders);
        }
        //吧当前订单放到订单列表内
        $this->pushOrder();
        //推送行情
        $this->pushMarket($price, $completes);
    }

    /**获取数量
     *
     * @param TxOrder $tx_in
     * @param TxOrder $tx_out
     *
     * @return float
     */
    public function getAmount(&$tx_in, &$tx_out)
    {
        if (bc($tx_in->amount, '>', $tx_out->amount)) {
            $amount         = $tx_out->amount;
            $tx_in->amount  = bc($tx_in->amount, '-', $amount);
            $tx_out->amount = 0;
        } elseif (bc($tx_in->amount, '<', $tx_out->amount)) {
            $amount         = $tx_in->amount;
            $tx_out->amount = bc($tx_out->amount, '-', $amount);
            $tx_in->amount  = 0;
        } else {
            $amount         = $tx_out->amount;
            $tx_in->amount  = 0;
            $tx_out->amount = 0;
        }
        return $amount;
    }

    /**撮合完成后更新单子
     *
     * @param $id
     * @param $completes
     * @param $fee
     * @param $way
     */
    public function updateOrder($id, $completes, $fee, $way)
    {
        UpdateTxOrder::dispatch($id, $completes, $fee, $way);
    }

    /**改变用户金额
     *
     * @param TxOrder $tx_in  买入挂单
     * @param TxOrder $tx_out 卖出挂单
     * @param float   $amount 成交量
     * @param float   $price  价格
     *
     * @return array
     */
    public function changeBalance($tx_in, $tx_out, $amount, $price)
    {
        //计算金额
        $volume = bc($amount, '*', $price);

        //扣过手续费的金额
        $dec_fee_amount = $this->getInFee($amount, $in_fee);
        $dec_fee_volume = $this->getOutFee($volume, $out_fee);

        //给买方加交易币金额,减少计价币
        AsyncChangeBalance::dispatch($tx_in->base_account_id, ChangeAccountLog::TX_MATCH,
            $dec_fee_amount);
        AsyncChangeBalance::dispatch($tx_in->quote_account_id, ChangeAccountLog::TX_MATCH,
            -$volume, AccountLog::IS_LOCK);

        //给卖方加计价币金额,减少交易币
        AsyncChangeBalance::dispatch($tx_out->quote_account_id, ChangeAccountLog::TX_MATCH,
            $dec_fee_volume);
        AsyncChangeBalance::dispatch($tx_out->base_account_id, ChangeAccountLog::TX_MATCH,
            -$amount, AccountLog::IS_LOCK);

        return [
            'in_fee'  => $in_fee,
            'out_fee' => $out_fee,
        ];
    }

    /**交易完成并且写完成记录
     *
     * @param float      $close     收盘价
     * @param Collection $completes 交易完成记录
     */
    public function pushMarket($close, $completes)
    {
        PushMarket::dispatch(
            $this->order->currency_match_id, $this->symbol, $close, $completes->take(30), null, CurrencyMatch::EXCHANGE
        )->onQueue('pushMarket');
    }

    /**获取买入手续费,返回扣过手续费的金额
     *
     * @param float $amount 成交量
     * @param float $fee    手续费
     *
     * @return bool|string|null
     */
    public function getInFee($amount, &$fee)
    {
        $rate   = $this->order->change_fee_rate;
        $fee    = bc($amount, '*', $rate);
        $amount = bc($amount, '-', $fee);
        return $amount;
    }

    /**获取卖出手续费,返回扣过手续费的金额
     *
     * @param float $amount 成交量
     * @param float $fee    手续费
     *
     * @return bool|string|null
     */
    public function getOutFee($amount, &$fee)
    {
        $rate   = $this->order->change_fee_rate;
        $fee    = bc($amount, '*', $rate);
        $amount = bc($amount, '-', $fee);
        return $amount;
    }

    /**取消订单
     *
     * @throws Exception
     */
    public function cancel()
    {
        $orders = $this->getOrders($this->symbol, $this->way);

        $order = $orders->pull($this->order->id);

        //不存在订单则不处理
        if (!$order) {
            Log::channel('match')->error('撤单失败,撮合引擎找不到单子');
            return;
        }

        $this->setOrders($this->symbol, $this->way, $orders);
        //推送行情
        $this->pushMarket(0, collect());

        //异步删除订单加钱
        CancelTxOrder::dispatch($this->order, $this->way);
    }

    /**获取字符串的way
     *
     * @param $way
     *
     * @return mixed|string
     */
    public static function getWayName($way)
    {
        $way_name = self::WAY_NAME[$way] ?? '';
        return $way_name;
    }

    /**设置订单
     *
     * @param string           $symbol 交易对符号
     * @param int              $way    方向
     * @param array|Collection $orders 订单
     *
     */
    public static function setOrders($symbol, $way, $orders)
    {
        if (is_array($orders)) {
            $orders = collect($orders);
        }
        if (!$orders instanceof Collection) {
            throw new ThrowException('设置订单到缓存失败,订单类型错误');
        }
        $way_name = self::getWayName($way);
        $key      = self::getCacheKey($symbol, $way_name);

        Cache::forever($key, $orders);

        return $orders;
    }

    /**获取订单
     *
     * @param string $symbol 交易对符号
     * @param int    $way    方向
     *
     * @return Collection
     * @throws Exception
     */
    public static function getOrders($symbol, $way)
    {
        $way_name = self::getWayName($way);
        $key      = self::getCacheKey($symbol, $way_name);

        $orders = Cache::get($key, collect());

        if (is_array($orders)) {
            $orders = collect($orders);
        }
        return $orders;
    }

    /**吧当前订单放到订单列表内
     *
     * @throws Exception
     */
    public function pushOrder()
    {
        //如果已经撮合完则不放到订单列表
        if ($this->order->amount <= 0) {
            return;
        }
        $orders = $this->getOrders($this->symbol, $this->way);

        //只是为了转成字符串
        $this->order->price = bc($this->order->price, '+', '0', 8);

        $orders[$this->order->id] = $this->order;

        if ($this->way == self::IN) {
            //买入按照价格倒序(价高者优)
            $orders = $orders->sortByDesc('price');
        }
        if ($this->way == self::OUT) {
            //卖出按照价格倒序(价低者优)
            $orders = $orders->sortBy('price');
        }
        $this->setOrders($this->symbol, $this->way, $orders);
    }

    /**获取缓存的键
     *
     * @param string $symbol   交易对符号
     * @param string $way_name 方向的字符串值
     *
     * @return string
     */
    public static function getCacheKey($symbol, $way_name)
    {
        $key = "match:order:{$symbol}:{$way_name}";
        return $key;
    }
}
