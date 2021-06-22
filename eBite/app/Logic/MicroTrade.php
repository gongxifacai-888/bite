<?php

namespace App\Logic;

use App\Events\Micro\MicroClosedEvent;
use App\Http\Controllers\Admin\HazardRateController;
use App\Models\Setting\Setting;
use App\Utils\Probability;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User\User;
use App\Models\Micro\{MicroOrder, MicroSecond};
use App\Models\Account\{Account, AccountLog, AccountType, MicroAccount};
use App\Models\Currency\{Currency, CurrencyMatch};

class MicroTrade
{
    /**
     * 添加一个交易订单
     *
     * @param array $param
     *
     * @return MicroOrder
     * @throws \Exception
     * @throws \Throwable
     */
    public static function addOrder($param)
    {
        list (
            'user_id' => $user_id,
            'type' => $type,
            'match_id' => $match_id,
            'currency_id' => $currency_id,
            'seconds' => $seconds,
            'price' => $price,
            'number' => $number,
            ) = $param;
        try {
            DB::beginTransaction();
            //检测用户是否存在
            $user = User::find($user_id);
            throw_unless($user, new \Exception('用户无效'));
            //检测交易对是否存在
            $currency_match = CurrencyMatch::findOrFail($match_id);
            throw_unless($currency_match, new \Exception('交易对不存在'));
            throw_unless($currency_match->open_microtrade, new \Exception('交易未开启'));
            $currency = Currency::findOrFail($currency_id);
            throw_unless($currency->micro_account_display, new \Exception('币种不允许被交易'));
            //检测秒数是否在合法范围内
            $seconds = MicroSecond::where('seconds', $seconds)->first();
            throw_unless($seconds, new \Exception('到期时间不允许'));
            //检测数量是否在合法范围内
            $number_not_exist = $currency->microNumbers->where('number', $number)->isEmpty();
            throw_if($number_not_exist, new \Exception('数量不在有效范围内'));
            //整数判断
            // if (!preg_match('/^\d+$/', $number)) {
            //     throw new \Exception('下单数量必须是整数');
            // }
            // if ($number % $currency->micro_min != 0) {
            //     throw new \Exception('下单数量必须是' . $currency->micro_min . '的整数倍');
            // }
            // if (bc($number, '>', $currency->micro_max)) {
            //     throw new \Exception('最大下单数量不能超过:' . $currency->micro_max);
            // }
            // if (bc($currency->micro_min, '>', $number)) {
            //     throw new \Exception('最小下单数量不能小于:' . $currency->micro_min);
            // }
            //扣款
            $account = MicroAccount::getAccountForLock($currency_id, $user_id);
            throw_unless($account, new \Exception("用户{$currency->code}币种对应账户不存在"));
            $account->changeBalance(AccountLog::MICRO_TRADE_ORDER, -$number);
            $fee = bc($currency->micro_trade_fee, '*', $number);
            $account->changeBalance(AccountLog::MICRO_TRADE_ORDER_FEE, -$fee);

            $now = Carbon::now();
            //生成交易数据
            $order_data = [
                'user_id' => $user_id,
                'match_id' => $currency_match->id,
                'currency_id' => $currency->id,
                'type' => $type,
                'seconds' => $seconds->seconds,
                'number' => $number,
                'open_price' => $price,
                'end_price' => $price,
                'profit_ratio' => $seconds->profit_ratio,
                'fee' => $fee,
                'status' => MicroOrder::STATUS_OPENED,
                'pre_profit_result' => 0, //预设赢利
                'handled_at' => $now->addSeconds($seconds->seconds),
                'agent_path' => $user->agent_path

            ];
            //添加一个交易
            $result = MicroOrder::unguarded(function () use ($order_data) {
                $micro_order = MicroOrder::create($order_data);
                return $micro_order;
            });
            if (Setting::getValueByKey('risk_probability_on', 0)) {
                self::riskByProbability($result);
            }
            DB::commit();
            return $result;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * 批量更新交易对的价格,已废弃,不要使用
     *
     * @param integer $match_id
     *
     * @return void
     */
    public static function newPrice($match_id, $price)
    {
        return false;
        MicroOrder::where('status', MicroOrder::STATUS_OPENED)
            ->where('match_id', $match_id)
            ->where('pre_profit_result', 0)
            ->update([
                'end_price' => $price,
            ]);
    }

    /**
     * 交易订单统计
     *
     * @param $orders
     *
     * @return array
     */
    public static function orderCount($orders)
    {
        //按盈亏进行分组
        $profit_group_order = $orders->groupBy('pre_profit_result');
        $shoud_price_list = [];
        $profit_group_order->each(function ($item, $key) use (&$shoud_price_list) {
            $result_type = $key;
            //按交易类型(买涨,买跌)来分组统计
            $type_group_orders = $item->groupBy('type');
            $type_group_orders->each(function ($item, $key) use ($result_type, &$shoud_price_list) {
                $submit_type = $key;
                list(
                    'name' => $price_name,
                    'price' => $right_price,
                    ) = self::countPrice($result_type, $submit_type, $item);
                $shoud_price_list[$result_type][$price_name . '_price'] = $right_price;
            });
        });
        return $shoud_price_list;
    }

    /**
     * 统计订单类型和盈亏推断价格
     *
     * @param integer    $result_type 盈亏(-1亏,1盈)
     * @param integer    $type        下单类型(买涨、买跌)
     * @param Collection $orders
     *
     * @return array
     */
    public static function countPrice($result_type, $type, $orders)
    {
        if ($result_type == MicroOrder::RESULT_LOSS) {
            //亏损处理
            if ($type == MicroOrder::TYPE_RISE) {
                //统计出买涨的最低价让行情价格低于最低价
                $name = 'min';
                $right_price = $orders->min('open_price');
            } elseif ($type == MicroOrder::TYPE_FALL) {
                //统计出买跌的最高价让行情价格高于最高价
                $name = 'max';
                $right_price = $orders->max('open_price');
            }
        } elseif ($result_type == MicroOrder::RESULT_PROFIT) {
            //盈利处理
            if ($type == MicroOrder::TYPE_RISE) {
                //统计出买涨的最高价让行情价格高于最高价
                $name = 'max';
                $right_price = $orders->max('open_price');
            } elseif ($type == MicroOrder::TYPE_FALL) {
                //统计出买跌的最低价让行情价格低于最低价
                $name = 'min';
                $right_price = $orders->min('open_price');
            }
        } else {
            //平局处理
        }
        return [
            'name' => $name,
            'price' => $right_price,
        ];
    }

    /**
     * 平仓
     *
     * @param int   $match_id
     *
     * @param float $close
     *
     * @return array|bool []|bool
     */
    public static function close($match_id, $close)
    {
        try {
            $currencyMatch = CurrencyMatch::find($match_id);
            if (!$currencyMatch) {
                return false;
            }
            $opened_orders = self::getNeedCloseOrder($match_id);
            if ($opened_orders->isEmpty()) {
                return false;
            }

            MicroOrder::whereIn('id', $opened_orders->pluck('id')->all())
                ->where('status', MicroOrder::STATUS_OPENED)
                ->update([
                    'status' => MicroOrder::STATUS_CLOSING,
                ]);

            $ids = $opened_orders->pluck('id')->all();

            //按处理时间分一下组,不然单控会有问题
            $opened_orders->groupBy(function ($order) {
                return $order->handled_at->toDateTimeString();
            })->each(function ($orders) use ($currencyMatch, $close) {
                //单控
                $close = self::orderRisk($currencyMatch, $orders, $close);

                foreach ($orders as $order) {
                    /**@var MicroOrder $order * */
                    $order->refresh();
                    $order->end_price = $close;
                    // 根据盈亏生成相关参数
                    if ($order->profit_type == MicroOrder::RESULT_PROFIT) {
                        //结算本金和利息
                        $capital = $order->number;
                        $fact_profit = bc($capital, '*', $order->profit_ratio);
                        $change = bc($capital, '+', $fact_profit);
                    } elseif ($order->profit_type == MicroOrder::RESULT_BALANCE) {
                        //结算本金,利息为0
                        $capital = $order->number;
                        $fact_profit = 0;
                        $change = $capital;
                    } elseif ($order->profit_type == MicroOrder::RESULT_LOSS) {
                        //本金填补亏损
                        $capital = 0;
                        $fact_profit = -$order->number;
                        $change = $capital;
                    }
                    $order->profit_result = $order->profit_type;
                    $order->status = MicroOrder::STATUS_CLOSED;
                    $order->fact_profits = $fact_profit;
                    $order->complete_at = Carbon::now();
                    $order->save();
                    // echo date('Y-m-d H:i:s ') . '===发送平仓通知消息===' . PHP_EOL;
                    event(new MicroClosedEvent($order));
                    // 查找钱包并结算
                    $account = Account::getAccountByType(AccountType::MICRO_ACCOUNT_ID, $order->currency_id,
                        $order->user_id);
                    $account->changeBalance(AccountLog::MICRO_TRADE_ORDER_CLOSE, $change);
                }
            });
            return $ids;

        } catch (\Throwable $th) {
            dump($th->getFile() . ',' . $th->getLine() . ',' . $th->getMessage());
        }
    }

    /**获取一个单控价格
     *
     * @param CurrencyMatch $currencyMatch
     * @param Collection    $orders
     * @param float         $close
     *
     * @return float|int
     */
    public static function orderRisk($currencyMatch, $orders, $close)
    {
        //实现单控
        //先查找出来一个被控制的单子
        $riskOrder = $orders->where('pre_profit_result', '<>', MicroOrder::RESULT_BALANCE)
            ->first();

        if (!$riskOrder) {
            return $close;
        }

        //如果有被控制的单子
        $rate = 1;
        //判断是否预设是亏的单子
        if ($riskOrder->pre_profit_result == MicroOrder::RESULT_LOSS) {
            //设置亏损
            if ($riskOrder->type == MicroOrder::TYPE_RISE && $close >= $riskOrder->open_price) {
                $rate -= $currencyMatch->order_risk_rate;
            }
            if ($riskOrder->type == MicroOrder::TYPE_FALL && $close <= $riskOrder->open_price) {
                $rate += $currencyMatch->order_risk_rate;
            }
        }
        //判断是否预设是盈的单子
        if ($riskOrder->pre_profit_result == MicroOrder::RESULT_PROFIT) {
            dump('预设盈利');
            //设置盈利
            if ($riskOrder->type == MicroOrder::TYPE_RISE && $close <= $riskOrder->open_price) {
                $rate += $currencyMatch->order_risk_rate;
            }
            if ($riskOrder->type == MicroOrder::TYPE_FALL && $close >= $riskOrder->open_price) {
                $rate -= $currencyMatch->order_risk_rate;
            }
        }
        if ($rate != 1) {
            $close = $riskOrder->open_price * $rate;
            Market::handleKline($currencyMatch->id, $currencyMatch->symbol, $close, 0, $currencyMatch->market_from);
        }
        return $close;
    }

    /**
     * 取需要平仓的交易
     *
     * @param integer $match_id
     *
     * @return Collection
     */
    public static function getNeedCloseOrder($match_id = 0)
    {
        $now = Carbon::now();
        //待平仓的任务,按交易对进行分组
        $lists = MicroOrder::where('status', MicroOrder::STATUS_OPENED)
            ->when($match_id, function ($query, $match_id) {
                $query->where('match_id', $match_id);
            })
            ->where('handled_at', '<=', $now)
            ->get();

        return $lists;
    }

    /**
     * 按概率进行风控
     *
     * @param MicroOrder $order 订单
     *
     * @return void
     */
    public static function riskByProbability($order)
    {
        //当前盈利概率
        $risk_profit_probability = Setting::getValueByKey('risk_profit_probability', 0);
        $risk_loss_probability = 100 - ($risk_profit_probability > 100 ? 100 : $risk_profit_probability);

        $probability_data = [
            [
                'pre_profit_result' => 1,
                'chance' => $risk_profit_probability,
            ],
            [
                'pre_profit_result' => -1,
                'chance' => $risk_loss_probability,
            ]
        ];
        $profit_result = Probability::lotteryRaffle($probability_data);
        $order->update([
            'pre_profit_result' => $profit_result['pre_profit_result']
        ]);
    }

}
