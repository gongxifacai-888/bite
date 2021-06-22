<?php


namespace App\Logic;

use App\Entity\Market\Depth;
use App\Entity\Market\DepthTx;
use App\Entity\Market\Kline;
use App\Entity\TxOrder;
use App\Events\Market\KlineEvent;
use App\Jobs\MatchEngine;
use App\Models\Currency\CurrencyMatch;
use App\Utils\Market\Huobi\Http\HuobiMarket;
use Faker\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Market
{
    const PERIODS = ['1min', '5min', '15min', '30min', '60min', '1day', '1week', '1mon'];

    //设置的越少,越省性能和带宽
    //默认保存和读取的k线条数
    const KLINE_SIZE = 60 * 6;

    /**
     * @param CurrencyMatch $currencyMatch
     * @param string        $other_symbol
     *
     */
    public static function importKline($currencyMatch, $other_symbol = null)
    {
        $get_kline_symbol = $other_symbol ?: $currencyMatch->lower_symbol;
        foreach (self::PERIODS as $period) {
            $kline_list = self::getDataFromHuobi($get_kline_symbol, $period, self::KLINE_SIZE);
            $key = "kline:{$currencyMatch->symbol}:{$period}";
            $arr = [];
            foreach ($kline_list as $kline) {
                $klineEntity = new Kline();

                $klineEntity->id = $kline['id'];
                $klineEntity->period = $period;
                $klineEntity->{"base-currency"} = $currencyMatch->baseCurrency->code;
                $klineEntity->{"quote-currency"} = $currencyMatch->quoteCurrency->code;
                $klineEntity->open = $kline['open'];
                $klineEntity->close = $kline['close'];
                $klineEntity->high = $kline['high'];
                $klineEntity->low = $kline['low'];

                $klineEntity->volume = $kline['vol'];
                $klineEntity->amount = $kline['amount'];
                $klineEntity->market_from = CurrencyMatch::HUOBI;
                $klineEntity->currency_match_id = $currencyMatch->id;

                $arr[$klineEntity->id] = $klineEntity;
            }
            ksort($arr);
            Cache::forever($key, $arr);
        }
    }

    /**写入k线
     * 插针也用这个
     *
     *
     * @param CurrencyMatch $match       交易对
     * @param float         $price       价格
     * @param float         $amount      交易量,如果插针可以传0
     * @param int           $market_from 行情来自哪里
     */
    public static function handleKline($match_id, $symbol, $price, $amount, $market_from)
    {
        $klines = Market::computeAllKline($match_id, $symbol, $amount, $price, $market_from);
        foreach ($klines as $key => $kline) {
            event(new KlineEvent($kline));
        }
    }

    /**清除k线
     *
     * @param $currencyMatch CurrencyMatch
     *
     */
    public static function clearKline($currencyMatch)
    {
        foreach (self::PERIODS as $period) {
            $key = "kline:{$currencyMatch->symbol}:{$period}";
            $arr = [];
            Cache::forever($key, $arr);
        }
    }

    /**从火币获取某个交易对的k线数据
     *
     * @param $symbol
     * @param $period
     *
     * @return array
     */
    public static function getDataFromHuobi($symbol, $period, $size = self::KLINE_SIZE)
    {
        $result = HuobiMarket::getHistoryKline($symbol, $period, $size);
        $result = json_decode(json_encode($result->data), true);
        return $result;
    }

    public static function getKline($symbol, $period, $size = self::KLINE_SIZE)
    {
        $key = "kline:{$symbol}:{$period}";
        $kline_list = Cache::get($key, []);
        $kline_list = collect($kline_list);
        $kline_list = $kline_list->sortKeysDesc()->take($size);
        return $kline_list->sortKeys()->values()->transform(function ($item, $key) {
            /**@var Kline $item * */
            $item->time = $item->id * 1000;
            return $item;
        });
    }

    /**更新所有类型的k线
     * 自己的撮合才用
     *
     * @param string $symbol
     * @param float  $amount
     * @param float  $price
     * @param int    $market_from
     *
     * @return array
     */
    public static function computeAllKline($currency_march_id, $symbol, $amount, $price, $market_from)
    {
        $timestamp = now()->timestamp;
        $klines = [];
        foreach (self::PERIODS as $period) {
            $klines[] = self::computePeriodKline($currency_march_id, $symbol, $period, $amount, $price, $timestamp,
                $market_from);
        }
        return $klines;
    }

    /**更新来自火币的k线
     *
     * @param $kline
     */
    public static function writeKline($kline)
    {
        $base_currency = $kline->{"base-currency"};
        $quote_currency = $kline->{"quote-currency"};
        $symbol = "{$base_currency}/{$quote_currency}";
        $period = $kline->period;
        // K线来源与交易对设置一致时才写入
        self::replaceSymbolKline($symbol, $period, $kline);
    }

    /**
     * @param Kline $kline
     *
     * @throws \Exception
     */
    public static function pushKline($kline)
    {
        $base_currency = $kline->{"base-currency"};
        $quote_currency = $kline->{"quote-currency"};
        $symbol = "{$base_currency}/{$quote_currency}";
        SocketPusher::kline($symbol, $kline);
    }

    /**更新火币来的k线
     *
     * @param $symbol
     * @param $period
     * @param $data 火币数据
     *
     * @return mixed
     */
    public static function replaceSymbolKline($symbol, $period, $kline)
    {
        $id = $kline->id;
        $key = "kline:{$symbol}:{$period}";
        $kline_list = Cache::get($key, []);
        $kline_list[$id] = $kline;
        if (count($kline_list) > self::KLINE_SIZE) {
            $delete_key = array_key_first($kline_list);
            unset($kline_list[$delete_key]);
        }
        Cache::forever($key, $kline_list);
        return $kline;
    }

    /**计算某种时间的k线
     *
     * @param string $symbol
     * @param string $period
     * @param float  $amount
     * @param float  $price
     * @param int    $timestamp
     * @param int    $market_from
     *
     * @return Kline
     */
    public static function computePeriodKline($currency_march_id, $symbol, $period, $amount, $price, $timestamp, $market_from)
    {
        $id = self::formatTimeline($period, $timestamp);
        $key = "kline:{$symbol}:{$period}";
        $kline_list = Cache::get($key, []);
        /**@var Kline $old_kline * */
        $old_kline = $kline_list[$id] ?? null;
        if ($old_kline) {
            //更新最后老k线
            $old_kline->close = $price;
            $old_kline->high < $price && $old_kline->high = $price;
            $old_kline->low > $price && $old_kline->low = $price;
            $old_kline->amount += $amount;
            $old_kline->volume += $price * $amount;
            $old_kline->market_from = $market_from;

            $kline = $old_kline;

        } else {
            list($base_currency, $quote_currency) = explode('/', $symbol);

            $kline = new Kline();

            //插入新的k线
            $kline->id = $id;
            $kline->period = $period;
            $kline->{"base-currency"} = $base_currency;
            $kline->{"quote-currency"} = $quote_currency;
            $kline->open = $price;
            $kline->close = $price;
            $kline->high = $price;
            $kline->low = $price;
            $kline->amount = $amount;
            $kline->volume = $price * $amount;
            $kline->market_from = $market_from;
            $kline->currency_match_id = $currency_march_id;
        }

        $kline_list[$id] = $kline;

        return $kline;
    }

    /**获取买入深度
     *
     * @param     $symbol
     * @param int $limit
     *
     * @return Collection
     * @throws \Exception
     */
    public static function depthBuys($symbol, $limit = 15)
    {
        $tx_buys = MatchEngine::getOrders($symbol, MatchEngine::IN);

        $tx_buys = $tx_buys->groupBy('price')->take($limit)->values()
            ->map(function ($buy_list) {

                $amount = 0;
                /**@var $buy_list Collection* */
                $buy_list->each(function ($buy) use (&$amount) {
                    /**@var TxOrder $buy * */
                    $amount = bc($amount, '+', $buy->amount, 4);
                });
                $price = $buy_list->pluck('price')->first();

                return new DepthTx($amount, parse_price($price));
            });

        return $tx_buys;
    }

    /**获取卖出深度
     *
     */
    public static function depthSells($symbol, $limit = 15)
    {
        $tx_sells = MatchEngine::getOrders($symbol, MatchEngine::OUT);

        $tx_sells = $tx_sells->groupBy('price')->take($limit)->values()
            ->map(function ($sell_list) {

                $amount = 0;
                /**@var $sell_list Collection* */
                $sell_list->each(function ($sell) use (&$amount) {
                    /**@var TxOrder $sell * */
                    $amount = bc($amount, '+', $sell->amount, 4);
                });

                $price = $sell_list->pluck('price')->first();

                return new DepthTx($amount, parse_price($price));
            });

        return $tx_sells;
    }

    /**
     * 按类型格式化时间线
     *
     * @param integer $type     类型:1.15分钟,2.1小时,3.一年,4.一天,5.分时,6.5分钟，7.30分钟,8.一周,9.一月,10.4小时
     * @param integer $day_time 时间戳,不传将默认采用当前时间
     *
     * @return int
     */
    public static function formatTimeline($type, $day_time = null)
    {
        empty($day_time) && $day_time = time();
        switch ($type) {
            //15分钟
            case '15min':
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 15);
                $minute = $multiple * 15;
                $time = $start_time + $minute * 60;
                break;
            //1小时
            case '60min':
                $time = strtotime(date('Y-m-d H:00:00', $day_time));
                break;
            //4小时
            case '4hour':
                $start_time = strtotime(date('Y-m-d', $day_time));
                $hours = intval(date('H', $day_time));
                $multiple = floor($hours / 4);
                $hours = $multiple * 4;
                $time = $start_time + $hours * 3600;
                break;
            //一天
            case '1day':
                $time = strtotime(date('Y-m-d', $day_time));
                break;
            //分时
            case '1min':
                $time_string = date('Y-m-d H:i', $day_time);
                $time = strtotime($time_string);
                break;
            //5分钟
            case '5min':
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 5);
                $minute = $multiple * 5;
                $time = $start_time + $minute * 60;
                break;
            //30分钟
            case '30min':
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 30);
                $minute = $multiple * 30;
                $time = $start_time + $minute * 60;
                break;
            //一周
            case '1week':
                $start_time = strtotime(date('Y-m-d', $day_time));
                $week = intval(date('w', $day_time));
                $diff_day = $week;
                $time = $start_time - $diff_day * 86400;
                break;
            //一月
            case '1mon':
                $time_string = date('Y-m', $day_time);
                $time = strtotime($time_string);
                break;
            //一年
            case '1year':
                $time = strtotime(date('Y-01-01', $day_time));
                break;
            default:
                $time = $day_time;
                break;
        }
        return $time;
    }
}
