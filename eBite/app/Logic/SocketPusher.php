<?php


namespace App\Logic;

use App\Models\Tx\Tx;
use Illuminate\Support\Collection;
use App\Entity\Market\Depth;
use App\Entity\Market\Kline;
use App\Models\Currency\CurrencyQuotation;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Cache;

class SocketPusher extends Gateway
{
    //发送目标
    const UID = 'UID';
    const CLIENT = 'CLIENT';
    const ALL = 'ALL';
    const GROUP = 'GROUP';

    //类型
    const KLINE = 'KLINE'; //k线
    const GLOBAL_TX = 'GLOBAL_TX'; //全站交易
    const LEVER_TRADE = 'LEVER_TRADE'; //合约交易
    const LEVER_CLOSED = 'LEVER_CLOSED'; //合约平仓
    const MARKET_DEPTH = 'MARKET_DEPTH'; //深度
    const MICRO_CLOSED = 'MICRO_CLOSED'; // 秒合约平仓
    const DAY_MARKET = 'DAY_MARKET'; //交易概要
    const TX_MATCHED = 'TX_MATCHED'; //币币交易订单被匹配
    const TX_SUBMIT = 'TX_SUBMIT'; //币币交易订单被匹配

    /**发送消息
     *
     * @param string $type
     * @param array  $data
     * @param string $target
     * @param mixed  $to
     *
     * @throws \Exception
     */
    public static function sendMsg($type, $data, $target = self::ALL, $to = 0)
    {
        $data['type'] = $type;
        $send_json = json_encode($data, JSON_UNESCAPED_UNICODE);

        switch ($target) {
            case self::ALL:
                self::sendToAll($send_json);
                break;
            case self::CLIENT:
                self::sendToClient($to, $send_json);
                break;
            case self::UID:
                self::sendToUid($to, $send_json);
                break;
            case self::GROUP:
                self::sendToGroup($to, $send_json);
                break;
        }
    }

    /**判断用户是否订阅
     *
     * @param $user_id
     * @param $group
     *
     * @return bool
     */
    public static function isInGroup($user_id, $group)
    {
        $group_uids = self::getUidListByGroup($group);
        return in_array($user_id, $group_uids);
    }

    /**发送合约订单信息
     *
     * @param array $data 订单数组
     * @param       $to
     *
     * @throws \Exception
     */
    public static function leverTrade($data, $to)
    {
        //已经在上层检测订阅,不用在这里检测了
        // 应先检测有没有订阅
//        $group_uids = self::getUidListByGroup(self::LEVER_TRADE);
//        if (in_array($to, $group_uids)) {
//            self::sendMsg(self::LEVER_TRADE, $data, self::UID, $to);
//        }
        self::sendMsg(self::LEVER_TRADE, $data, self::UID, $to);
    }

    /**发送合约平仓信息
     *
     * @param $data 订单id[]
     * @param $to
     *
     * @throws \Exception
     */
    public static function leverClosed($order_ids, $to)
    {
        // 应先检测有没有订阅
        $group_uids = array_merge(self::getUidListByGroup(self::LEVER_CLOSED),
            self::getUidListByGroup(self::LEVER_CLOSED));
        if (in_array($to, $group_uids)) {
            self::sendMsg(self::LEVER_CLOSED, ['id' => $order_ids,], self::UID, $to);
        }
    }

    /**k线
     *
     * @param string $symbol
     * @param Kline  $kline
     *
     * @throws \Exception
     */
    public static function kline($symbol, $kline)
    {
        $symbol = strtoupper($symbol);
        $group = self::KLINE . '.' . $symbol;
        $data = [
            'symbol' => $symbol,
            'kline' => $kline,
            'currency_match_id' => $kline->currency_match_id,
        ];
        self::sendMsg(self::KLINE, $data, self::GROUP, $group);
    }

    /**全站交易
     *
     * @param string     $symbol
     * @param Collection $completes 实体类的TxCompletes
     * @param int        $currency_match_id
     *
     * @throws \Exception
     */
    public static function globalTx($symbol, $completes, $currency_match_id = 0)
    {
        $symbol = strtoupper($symbol);
        $data = [
            'symbol' => $symbol,
            'currency_match_id' => $currency_match_id,
            'completes' => $completes->take(30)->toArray(),
        ];

        $group = self::GLOBAL_TX . '.' . $symbol;
        self::sendMsg(self::GLOBAL_TX, $data, self::GROUP, $group);
    }

    /**深度
     *
     * @param string $symbol
     * @param Depth  $depth
     * @param int    $currency_match_id
     *
     * @throws \Exception
     */
    public static function marketDepth($depth)
    {
        $symbol = strtoupper($depth->symbol);
        $group = self::MARKET_DEPTH . '.' . $symbol;
        $data = [
            'symbol' => $depth->symbol,
            'currency_match_id' => $depth->currency_match_id,
            'depth' => $depth
        ];
        self::sendMsg(self::MARKET_DEPTH, $data, self::GROUP, $group);
    }

    /**日行情
     *
     * @param string            $symbol
     * @param CurrencyQuotation $quotation
     * @param int               $currency_match_id
     *
     * @throws \Exception
     */
    public static function dayMarket($symbol, $quotation, $currency_match_id = 0)
    {
        $symbol = strtoupper($symbol);
        $data = [
            'symbol' => $symbol,
            'currency_match_id' => $currency_match_id,
            'quotation' => $quotation->toArray(),
        ];
        self::sendMsg(self::DAY_MARKET, $data, self::GROUP, self::DAY_MARKET);
    }

    /**
     * 秒合约订单平仓完成
     *
     * @param \App\Models\Micro\MicroOrder $order
     *
     * @return void
     * @throws \Exception
     */
    public static function microClosed($order)
    {
        // 应先检测有没有订阅
        $group_uids = self::getUidListByGroup(self::MICRO_CLOSED);
        $data = [
            'order' => $order,
        ];
        if (!in_array($order->user_id, $group_uids)) {
            return;
        }
        self::sendMsg(self::MICRO_CLOSED, $data, self::UID, $order->user_id);
    }

    /**
     * 撮合订单被匹配
     *
     * @param array $order 订单
     * @param int   $way   买入还是卖出
     *
     * @return void
     * @throws \Exception
     */
    public static function txOrderMatched($order, $way)
    {
        // 应先检测有没有订阅
        $group_uids = self::getUidListByGroup(self::TX_MATCHED);
        $data = [
            'order' => $order,
            'way' => $way,
        ];
        if (!in_array($order['user_id'], $group_uids)) {
            return;
        }
        self::sendMsg(self::TX_MATCHED, $data, self::UID, $order['user_id']);
    }

    /**
     * 挂单推送
     *
     * @param array $order 订单
     * @param int   $way   买入还是卖出
     *
     * @return void
     * @throws \Exception
     */
    public static function txOrderSubmit($order, $way)
    {
        // 应先检测有没有订阅
        $group_uids = self::getUidListByGroup(self::TX_SUBMIT);
        $data = [
            'order' => $order,
            'way' => $way,
        ];
        if (!in_array($order['user_id'], $group_uids)) {
            return;
        }
        self::sendMsg(self::TX_SUBMIT, $data, self::UID, $order['user_id']);
    }
}
