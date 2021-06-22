<?php

namespace App\Listeners\Market;

use App\Entity\Market\Kline;
use App\Logic\SocketPusher;
use App\Models\Currency\CurrencyQuotation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\Market\KlineEvent;
use App\Jobs\ClearTxOrder;
use App\Jobs\LeverUpdate;
use App\Jobs\MicroUpdate;
use App\Logic\Market;
use App\Models\Currency\CurrencyMatch;
use App\Models\User\User;

class KlineListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param KlineEvent $event
     *
     * @return void
     * @throws \Exception
     */
    public function handle(KlineEvent $event)
    {
        $kline         = $event->kline;
        $currencyMatch = CurrencyMatch::findMatch($kline->currency_match_id);
        if (!$currencyMatch) {
            return;
        }
        if ($currencyMatch->market_from != $kline->market_from) {
            return;
        }

        $kline = $this->preHandleKline($kline, $currencyMatch);

        //推送k线
        $this->pushKline($kline);

        // 匹配掉用户的撮合挂单
        $this->clearTxOrder($currencyMatch, $kline);

        //合约发送价格处理
        $this->leverUpdate($currencyMatch, $kline);

        //处理交易概要(天行情)
        $this->dayMarket($currencyMatch, $kline);

        $this->microUpdate($currencyMatch, $kline);
    }

    /**
     * 预处理K线
     *
     * @param Kline                              $kline
     * @param \App\Models\Currency\CurrencyMatch $currencyMatch
     *
     * @return Kline
     */
    public function preHandleKline($kline, $currencyMatch)
    {
        $amount        = $kline->amount;
        $volume        = $kline->volume;
        $kline         = Market::computePeriodKline(
            $currencyMatch->id,
            $currencyMatch->symbol,
            $kline->period,
            0,
            $kline->close,
            $kline->id,
            $kline->market_from
        );
        $kline->amount = $amount;
        $kline->volume = $volume;
        return $kline;
    }

    /**
     * @param CurrencyMatch $currencyMatch
     * @param Kline         $kline
     *
     * @throws \Exception
     */
    public function pushKline($kline)
    {
        Market::pushKline($kline);
        Market::writeKline($kline);
    }

    /**
     * @param CurrencyMatch $currencyMatch
     * @param Kline         $kline
     *
     * @throws \Exception
     */
    public function leverUpdate($currencyMatch, $kline)
    {
        //如果不是一分钟的行情,不处理
        if ($kline->period != '1min' || !$currencyMatch->open_lever || !bc($kline->close, '>', '0')) {
            return;
        }
        $params = [
            'currency_match' => $currencyMatch,
            'now_price'      => $kline->close,
            'now'            => microtime(true),
        ];
        LeverUpdate::dispatch($params)->onQueue('lever:update');
    }


    /**
     * @param CurrencyMatch $currencyMatch
     * @param Kline         $kline
     *
     * @throws \Exception
     */
    public function dayMarket($currencyMatch, $kline)
    {
        if ($kline->period != "1day") {
            return;
        }
        $day_data['close']  = $kline->close;
        $day_data['vol']    = $kline->volume;
        $day_data['amount'] = $kline->amount;
        $day_data['open']   = $kline->open;
        $day_data['low']    = $kline->low;
        $day_data['high']   = $kline->high;

        $day_data['change'] = ($day_data['close'] - $day_data['open']) / $day_data['close'] * 100;
        $day_data['change'] = bc($day_data['change'], '+', 0, 2);

        /**@var \App\Models\Currency\CurrencyQuotation $quotation * */
        $quotation = CurrencyQuotation::where('currency_match_id', $currencyMatch->id)->firstOrNew([
            'currency_match_id' => $currencyMatch->id
        ]);
        $quotation->replaceQuotation($day_data);
        SocketPusher::dayMarket($currencyMatch->symbol, $quotation, $currencyMatch->id);
    }

    /**
     * 更新秒合约价格
     *
     * @param \App\Models\Currency\CurrencyMatch $currencyMatch
     * @param \App\Entity\Market\Kline           $kline
     *
     * @return void
     */
    public function microUpdate($currencyMatch, $kline)
    {
        //如果不是一分钟的行情,不处理
        if ($kline->period != '1min' || !$currencyMatch->open_microtrade || !bc($kline->close, '>', '0')) {
            return;
        }
        $quque_number = $currencyMatch->id % 10;
        $quque_number = 0;
        $quque_number = substr("0{$quque_number}", -2);
        MicroUpdate::dispatch($kline)->onQueue("micro:update:{$quque_number}");
    }

    public function clearTxOrder($currencyMatch, $kline)
    {
        $currencyMatch->refresh();
        if (
            !$currencyMatch->auto_match
            || $kline->period != '1min'
            || $kline->market_from == CurrencyMatch::EXCHANGE
        ) {
            return;
        }
        ClearTxOrder::dispatch($currencyMatch, $kline->close);
    }
}
