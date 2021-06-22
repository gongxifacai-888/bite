<?php


namespace App\Logic;

use App\BlockChain\BlockChain;
use App\Entity\Market\Depth;
use App\Entity\Market\DepthTx;
use App\Entity\TxCompleteEntity;
use App\Entity\TxOrder;
use App\Jobs\ClearTxOrder;
use App\Jobs\MatchEngine;
use App\Jobs\PushMarket;
use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\Robot as RobotModel;
use App\Models\Tx\TxComplete;
use App\Models\User\User;
use Faker\Factory;
use Illuminate\Support\Facades\Cache;

class Robot
{

    /***当前推送价格
     *
     * @var float
     */
    protected $price;

    /**推送数量
     *
     * @var float
     */
    protected $amount;

    /**
     * @var CurrencyMatch
     */
    protected $currencyMatch;

    /**获取的火币基准价
     *
     * @var float
     */
    protected $huobi_first_price = 0;

    /**机器人价格
     *
     * @var float
     */
    protected $robot_price = 0;

    /**最后一次推送的价格
     *
     * @var float
     */
    protected $last_push_price = 0;

    /**
     * @var \App\Models\Tx\Robot
     */
    protected $robot;

    protected $process_id;

    protected $updated_at;

    public function __construct($process_id)
    {
        $this->robot_price = 0;
        $this->huobi_first_price = 0;

        $this->process_id = $process_id;
        dump('机器人运行开始:pid:' . $process_id);
    }

    public function init()
    {
        $this->robot = RobotModel::where('process_id', $this->process_id)->first();
        if (!$this->robot) {
            dump('机器人不存在pid:' . $this->process_id);
            return false;
        }
        if ($this->updated_at != $this->robot->updated_at) {
            $this->robot_price = 0;
            $this->huobi_first_price = 0;
        }
        if ($this->robot->status == RobotModel::STATUS_OFF) {
            return false;
        }
        if ($this->robot->currencyMatch()->value('market_from') != CurrencyMatch::ROBOT) {
            return false;
        }

        $this->updated_at = $this->robot->updated_at;
        dump('机器人初始化完成:pid:' . $this->process_id);
        return true;
    }

    public function run()
    {
        try {

            if (!$this->init()) {
                return;
            }

            $this->currencyMatch = $this->robot->currencyMatch;

            dump('运行' . now()->toDateTimeString() . ',pid:' . $this->process_id);
            $this->getPrice();
            $this->getAmount();

            $this->generateMarket();

        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getFile());
            dump($t->getLine());
        }

    }

    public function getMinPrice()
    {
        $decimal = $this->robot->decimal;
        $min_price = BlockChain::convertLower(1, $decimal);
        return $min_price;
    }


    /**直接生成行情
     *
     * @throws \Exception
     */
    public function generateMarket()
    {

        if (!$this->price) {
            dump('价格错误');
            return;
        }
        if (!$this->amount) {
            dump('交易量错误');
            return;
        }

        //生成随机完成单
        $completes = collect();

        if ($this->last_push_price > $this->price) {
            $way = TxComplete::OUT;
        } else {
            $way = TxComplete::IN;
        }

        if ($this->last_push_price != $this->price) {
            $faker = Factory::create();
            do {
                $amount = $faker->randomFloat(4, 1, $this->amount);
                $this->amount -= $amount;
                if ($this->amount <= 1) {
                    $this->amount = 1;
                }

                $txCompleteEntity = new TxCompleteEntity();
                $txCompleteEntity->in_user_id = $this->currencyMatch->match_user_id;
                $txCompleteEntity->out_user_id = $this->currencyMatch->match_user_id;
                $txCompleteEntity->price = $this->price;
                $txCompleteEntity->amount = $amount;
                $txCompleteEntity->way = $way;
                $txCompleteEntity->currency_match_id = $this->currencyMatch->id;

                $completes->prepend($txCompleteEntity);
            } while ($this->amount > 1);
        }

        $depth_buys = $this->virtualDepthBuys($this->currencyMatch->symbol, $this->price);
        $depth_sells = $this->virtualDepthSells($this->currencyMatch->symbol, $this->price);
        $depth = new Depth($this->currencyMatch->symbol, $depth_buys, $depth_sells, $this->currencyMatch->id);

        PushMarket::dispatch($this->currencyMatch->id, $this->currencyMatch->symbol, $this->price,
            $completes, $depth, CurrencyMatch::ROBOT)->onQueue('pushMarket')->onConnection('sync');

        $this->last_push_price = $this->price;
    }

    /**虚拟买入深度
     *
     * @param     $symbol
     * @param     $price
     * @param int $limit
     *
     * @return \Illuminate\Support\Collection
     */
    public function virtualDepthBuys($symbol, $price, $limit = 10)
    {
        $min_price = $this->getMinPrice();
        $faker = Factory::create();

        $buys = collect();
        for ($i = 0; $i < $limit; $i++) {
            $amount = $faker->randomFloat(8 - $this->robot->decimal, 200, 2000);
            $buy_price = bc($price - ($i + 1) * $min_price,'+', 0, $this->robot->decimal);

            $depthSell = new DepthTx($amount, $buy_price);
            $buys->push($depthSell);
        }
        return $buys;
    }

    /**虚拟卖出深度
     *
     * @param     $symbol
     * @param     $price
     * @param int $limit
     *
     * @return \Illuminate\Support\Collection
     */
    public function virtualDepthSells($symbol, $price, $limit = 10)
    {
        $min_price = $this->getMinPrice();

        $faker = Factory::create();

        $sells = collect();
        for ($i = 0; $i < $limit; $i++) {
            $amount = $faker->randomFloat(8 - $this->robot->decimal, 200, 1000);
            $out_price = bc($price + ($i + 1) * $min_price,'+', 0, $this->robot->decimal);

            $depthSell = new DepthTx($amount, $out_price);
            $sells->push($depthSell);
        }
        return $sells;
    }

    /**获取价格
     *
     */
    public function getPrice()
    {
        $url = 'https://api.huobi.br.com/market/trade?symbol=' . $this->robot->virtual_symbol;
        $info = http($url);
        $close = $info['tick']['data'][0]['price'];

        if (!$this->huobi_first_price) {
            $this->huobi_first_price = $close;
        }
        if (!$this->robot_price) {
            $this->robot_price = $this->robot->price;
        }

        $price = $this->robot_price + ($close - $this->huobi_first_price);
        $price = parse_price($price, null, 4);
        $this->price = $price;

        if ($this->price > $this->robot->price * 1.05 || $this->price < $this->robot->price * 0.95) {
            dump('重置基础价');
            $this->huobi_first_price = $close;
            $this->robot_price = $this->robot->price;
            $this->getPrice();
        }

    }

    /**获取交易量
     *
     */
    public function getAmount()
    {
        $faker = Factory::create();

        $number = $faker->randomFloat(4, 100, 1000);

        $this->amount = parse_number($this->price, $number);
//        $this->valume = parse_number($this->price, $this->robot->volume);
    }
}
