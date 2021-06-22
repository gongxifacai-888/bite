<?php

namespace App\Utils\Market\Huobi\WebSocket;

use App\Entity\Market\Depth;
use App\Entity\Market\Kline;
use App\Entity\Market\DepthTx;
use App\Entity\Market\TxSell;
use App\Entity\TxCompleteEntity;
use App\Events\Market\DepthEvent;
use App\Events\Market\KlineEvent;
use App\Logic\Market;
use App\Logic\SocketPusher;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer;
use App\Models\Currency\CurrencyMatch;
use App\Models\Currency\CurrencyQuotation;
use App\Models\Tx\TxComplete;
use App\Jobs\LeverUpdate;


class HuobiConnection
{
    protected $server_address = 'ws://api.huobi.pro:443/ws';
    // protected $server_address = 'ws://api.huobi.br.com:443/ws'; //ws国内开发调试
    protected $server_ping_freq = 10; //服务器ping检测周期,单位秒
    protected $server_time_out = 2; //服务器响应超时
    protected $send_freq = 1; //写入和发送数据的周期，单位秒

    protected $worker_id;

    protected $events = [
        'onConnect',
        'onClose',
        'onMessage',
        'onError',
        'onBufferFull',
        'onBufferDrain',
    ];

    protected static $marketKlineData = [];
    protected static $marketDepthData = []; //盘口深度数据
    protected static $matchTradeData = []; //撮合交易全站交易

    protected $connection;

    protected $timer;

    /**
     * @var Kline
     */
    protected $kline;

    protected $kline_timer;

    protected $pingTimer;

    protected $sendKlineTimer;

    protected $sendDepthTimer;

    protected $sendMatchTradeTimer;

    protected $handleTimer;

    protected $subscribed = [];

    protected $topicTemplate = [
        'sub' => [
            'market_kline' => 'market.$symbol.kline.$period', //K线
//            'market_detail' => 'market.$symbol.detail',
            'market_depth' => 'market.$symbol.depth.$type', //盘口深度
            'market_trade' => 'market.$symbol.trade.detail', //成交的交易
        ],
    ];

    public function __construct($worker_id)
    {
        $this->worker_id = $worker_id;
        AsyncTcpConnection::$defaultMaxPackageSize = 1048576000;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 绑定所有事件到连接
     *
     * @return void
     */
    protected function bindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = [$this, $event];
                //echo '绑定' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    /**
     * 解除连接所有绑定事件
     *
     * @return void
     */
    protected function unBindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = null;
                //echo '解绑' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    public function getSubscribed($topic = null)
    {
        if (is_null($topic)) {
            return $this->subscribed;
        }
        return $this->subscribed[$topic] ?? null;
    }

    protected function setSubscribed($topic, $value)
    {
        $this->subscribed[$topic] = $value;
    }

    protected function delSubscribed($topic)
    {
        unset($this->subscribed[$topic]);
    }

    public function connect()
    {
        $this->connection = new AsyncTcpConnection($this->server_address);
        $this->bindEvent();
        $this->connection->transport = 'ssl';
        $this->connection->connect();
    }

    public function onConnect($con)
    {
        //连接成功后定期发送ping数据包检测服务器是否在线
        $this->timer = Timer::add($this->server_ping_freq, [$this, 'ping'], [$this->connection], true);
        //添加订阅事件代码
        $this->subscribe($con);

        //定时将k线写入
        $this->kline_timer = Timer::add(1, [$this, 'klineTimer']);
    }

    /**定时更新k线的方法
     *
     */
    public function klineTimer()
    {
        if ($this->kline) {
            foreach ($this->kline as $kline) {
                event(new KlineEvent($kline));
            }
        }
    }

    public function onClose($con)
    {
        echo $this->server_address . '连接关闭' . PHP_EOL;
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $this->server_address . '连接关闭' . PHP_EOL, 3, $path . $filename);
        //解除事件
        $this->timer && Timer::del($this->timer);
//        $this->sendKlineTimer && Timer::del($this->sendKlineTimer);
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->depthTimer && Timer::del($this->depthTimer);
        $this->handleTimer && Timer::del($this->handleTimer);

        $this->unBindEvent();
        unset($this->connection);
        $this->connection = null;
        $this->subscribed = null; //清空订阅
        echo '尝试重新连接' . PHP_EOL;
        $this->connect();
    }

    public function close($msg)
    {
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $msg, 3, $path . $filename);
        $this->connection->destroy();
    }

    protected function makeSubscribeTopic($topic_template, $param)
    {
        $need_param = [];
        $match_count = preg_match_all('/\$([a-zA-Z_]\w*)/', $topic_template, $need_param);
        if ($match_count > 0 && count(reset($need_param)) > count($param)) {
            throw new \Exception('所需参数不匹配');
        }
        $diff = array_diff(next($need_param), array_keys($param));
        if (count($diff) > 0) {
            throw new \Exception('topic:' . $topic_template . '缺少参数：' . implode(',', $diff));
        }
        return preg_replace_callback('/\$([a-zA-Z_]\w*)/', function ($matches) use ($param) {
            extract($param);
            $value = $matches[1];
            return $$value ?? '';
        }, $topic_template);
    }

    public function onBufferFull()
    {
        echo 'buffer is full' . PHP_EOL;
    }

    protected function subscribe($con)
    {
        $periods = Market::PERIODS; //['1day', '1min'];
        if ($this->worker_id < 8) {
            $value = $periods[$this->worker_id];
            // echo '进程'. $this->worker_id . '开始订阅' . $value . '数据' . PHP_EOL;
            $this->subscribeKline($con, $value); //订阅k线行情
        } else {
            if ($this->worker_id == 8) {
                $this->subscribeMarketDepth($con); //订阅盘口数据
                $this->subscribeMarketTrade($con); //订阅全站交易数据
            }
        }
    }

    //订阅回调
    protected function onSubscribe($data)
    {
        if ($data->status == 'ok') {
            echo date('Y-m-d H:i:s ') . $data->subbed . '订阅成功' . PHP_EOL;
        } else {
            echo '订阅失败:' . $data->{'err-msg'} . PHP_EOL;
        }
    }

    //订阅K线行情
    protected function subscribeKline($con, $period)
    {
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->lower_symbol,
                'period' => $period,
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_kline'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                //'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            //未订阅过的才能订阅
            $subscribed_data = $this->getSubscribed($topic);
            if (is_null($subscribed_data)) {
                $this->setSubscribed($topic, [
                    'callback' => 'onMarketKline',
                    'match' => $value,
                ]);
                $con->send($sub_data);
            }
        }
    }

    protected function onMarketKline($con, $data, $match)
    {
        $topic = $data->ch;
        $msg = date('Y-m-d H:i:s') . ' 进程' . $this->worker_id . '接收' . $topic . '行情' . PHP_EOL;
        list($name, $symbol, $detail_name, $period) = explode('.', $topic);
        list($base_currency, $quote_currency) = explode('/', $match->symbol);
        $tick = $data->tick;
        $kline = new Kline();
        $kline->id = $tick->id;
        $kline->period = $period;
        $kline->{"base-currency"} = $base_currency;
        $kline->{"quote-currency"} = $quote_currency;
        $kline->open = sctonum($tick->open);
        $kline->close = sctonum($tick->close);
        $kline->high = sctonum($tick->high);
        $kline->low = sctonum($tick->low);
        $kline->volume = sctonum($tick->vol);
        $kline->amount = sctonum($tick->amount);
        $kline->market_from = CurrencyMatch::HUOBI;
        $kline->currency_match_id = $match->id;

        $this->kline[$match->symbol] = $kline;
//        event(new KlineEvent($kline));

    }

    // 订阅盘口数据
    protected function subscribeMarketDepth($con, $step = 'step0')
    {
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->lower_symbol,
                'type' => $step,
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_depth'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
            ]);

            $subscribed_data = $this->getSubscribed($topic);

            // 未订阅过的才能订阅
            if (is_null($subscribed_data)) {
                $this->setSubscribed($topic, [
                    'callback' => 'onMarketDepth',
                    'match' => $value,
                ]);
                $con->send($sub_data);
            }
        }
    }

    /**盘口数据回调
     *
     * @param AsyncTcpConnection $con
     * @param                    $data
     * @param CurrencyMatch      $match
     */
    protected function onMarketDepth($con, $data, $match)
    {
        try {
            $topic = $data->ch;
            $subscribed_data = $this->getSubscribed($topic);

            $limit = 10;
            $tick = $data->tick;
            $bids = array_slice($tick->bids, 0, $limit);
            $asks = array_slice($tick->asks, 0, $limit);

            $depth = new Depth($match->symbol, null, null, $match->id);

            foreach ($bids as $k => $v) {
                $txBuy = new DepthTx(parse_number($v[0], $v[1]), parse_price($v[0]));
                $depth->pushTxBuy($txBuy);
            }
            foreach ($asks as $k => $v) {
                $txSell = new DepthTx(parse_number($v[0], $v[1]), parse_price($v[0]));
                $depth->pushTxSell($txSell);
            }
            //推送深度
            event(new DepthEvent($depth, CurrencyMatch::HUOBI));
        } catch (\Exception $e) {
            dump('--------------------------------------------');
            echo $e->getMessage();
            echo $e->getFile();
            echo $e->getLine();
        }
    }

    /**
     * 订阅全站交易(已完成)
     *
     * @param mixed $name
     *
     * @return void
     */
    public function subscribeMarketTrade($con)
    {
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->lower_symbol,
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_trade'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
            ]);
            $subscribed_data = $this->getSubscribed($topic);
            // 未订阅过的才能订阅
            if (is_null($subscribed_data)) {
                $this->setSubscribed($topic, [
                    'callback' => 'onMatchTrade',
                    'match' => $value,
                ]);
                $con->send($sub_data);
            }
        }
    }

    //撮合交易全站交易数据回调
    protected function onMatchTrade($con, $data, $match)
    {
        $topic = $data->ch;
        $data = $data->tick->data;
        $timestamp = now()->timestamp;
        if ($data[0]->direction == 'buy') {
            $way = TxComplete::IN;
        } else {
            $way = TxComplete::OUT;
        }
        $completes = collect();
        $complete = new TxCompleteEntity();
        $complete->price = $data[0]->price;
        $complete->amount = $data[0]->amount;
        $complete->timestamp = $timestamp;
        $complete->way = $way;
        $completes->prepend($complete);
        SocketPusher::globalTx($match->symbol, $completes, $match->id);
    }


    //取消订阅
    protected function unsubscribe()
    {
    }

    protected function onUnsubscribe()
    {
    }

    public function onMessage($con, $data)
    {
        try {
            $data = gzdecode($data);
            $data = json_decode($data, false, 512, JSON_BIGINT_AS_STRING); //对特大整数的处理，避免转换成科学记数法
            if (isset($data->ping)) {
                $this->onPong($con, $data);
            } elseif (isset($data->pong)) {
                $this->onPing($con, $data);
            } elseif (isset($data->id) && $this->getSubscribed($data->id) != null) {
                $this->onSubscribe($data);
            } elseif (isset($data->id)) {

            } else {
                $this->onData($con, $data);
            }
        } catch (\Throwable $th) {
            echo "解析火币数据发生异常:" . $th->getMessage() . ",位于文件:" . $th->getFile() . ",第" . $th->getLine() . "行" . PHP_EOL;
        }
    }

    protected function onData($con, $data)
    {
        if (isset($data->ch)) {
            $subscribed = $this->getSubscribed($data->ch);
            if ($subscribed != null) {
                //调用回调处理
                $callback = $subscribed['callback'];
                $this->$callback($con, $data, $subscribed['match']);
            } else {
                //不在订阅中的数据
            }
        } else {
            echo '未知数据' . PHP_EOL;
            dump($data);
        }
    }

    protected function calcIncreasePair($daymarket_data)
    {
        $open = $daymarket_data['open'];
        $close = $daymarket_data['close'];;
        $change_value = bc($close, '-', $open);
        $change = bc(bc($change_value, '/', $open), '*', 100, 2);
        return $change;
    }

    //心跳响应
    protected function onPong($con, $data)
    {
        //echo '收到心跳包,PING:' . $data->ping . PHP_EOL;
        $send_data = [
            'pong' => $data->ping,
        ];
        $send_data = json_encode($send_data);
        $con->send($send_data);
        //echo '已进行心跳响应' . PHP_EOL;
    }

    public function ping($con)
    {
        $ping = time();
        //echo '进程' . $this->worker_id . '发送ping服务器数据包,ping值:' . $ping . PHP_EOL;
        $send_data = json_encode([
            'ping' => $ping,
        ]);
        $con->send($send_data);
        // $this->pingTimer = Timer::add($this->server_time_out, function () use ($con) {
        //     $msg = '进程' . $this->worker_id . '服务器响应超时,连接关闭' . PHP_EOL;
        //     echo $msg;
        //     $this->close($msg);
        // }, [], false);
    }

    protected function onPing($con, $data)
    {
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->pingTimer = null;
        //echo '进程' . $this->worker_id . '服务器正常响应中,pong:' . $data->pong. PHP_EOL;
    }
}
