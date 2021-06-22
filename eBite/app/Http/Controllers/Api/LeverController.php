<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Account\{Account, AccountType, LeverAccount, AccountLog};
use App\Models\Currency\{Currency, CurrencyMatch, CurrencyQuotation};
use App\Models\Lever\{LeverMultiple, LeverTransaction};
use App\Models\Setting\Setting;
use App\Models\Tx\{TxIn, TxOut};
use App\Models\User\User;
use App\Events\Lever\LeverSubmitOrderEvent;
use App\Jobs\{LeverClosing};

class LeverController extends Controller
{
    /**
     * 取交易信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deal(Request $request)
    {
        $user_id = User::getUserId();
        $currency_match_id = $request->get("currency_match_id");
        if (empty($currency_match_id)) {
            return $this->error(__("api.参数错误"));
        }
        $lever_share_limit = [
            'min' => 1,
            'max' => 0,
        ];
        $curreny_match = CurrencyMatch::findOrFail($currency_match_id);
        $lever_share_limit = array_merge($lever_share_limit, [
            'min' => $curreny_match->lever_min_share,
            'max' => $curreny_match->lever_max_share,
        ]);

        $my_transaction = LeverTransaction::with('user')
            ->orderBy('id', 'desc')
            ->where("user_id", $user_id)
            ->where("status", LeverTransaction::STATUS_TRANSACTION)
            ->where("quote_currency_id", $curreny_match->quote_currency_id)
            ->where("base_currency_id", $curreny_match->base_currency_id)
            ->orderBy("id", "desc")
            ->take(10)
            ->get();
        $last_price = $curreny_match->getLastPrice();
        $user_lever = 0;
        $all_levers = 0;
        if (!empty($user_id)) {
            $quote = LeverAccount::where("user_id", $user_id)
                ->where("currency_id", $curreny_match->quote_currency_id)
                ->first();
            if ($quote) {
                $user_lever = $quote->balance;
            }
            $all_levers = LeverTransaction::where("quote_currency_id", $curreny_match->quote_currency_id)
                ->where("base_currency_id", $curreny_match->base_currency_id)
                ->where("user_id", $user_id)
                ->where("status", LeverTransaction::STATUS_TRANSACTION)
                ->selectRaw('sum(`number` * `price`) as `all_levers`')
                ->value('all_levers');
            $all_levers || $all_levers = 0;
        }
        // $lever_transaction = $this->getLastLeverTransaction($curreny_match->quote_currency_id, $curreny_match->base_currency_id);
        $ustd_price = 0;
        $last = CurrencyQuotation::orderBy('id', 'desc')
            ->where("currency_match_id", $curreny_match->id)
            ->first();
        if (!empty($last)) {
            $ustd_price = $last->close;
        }
        if ($curreny_match->quote_currency_id == 3) {
            $ustd_price = 1;
        }


        $quotation = CurrencyQuotation::with('currencyMatch.quoteCurrency')
            ->where('currency_match_id', $currency_match_id)->first()
            ->append('cny_price');

        return $this->success("", [
            'quotation' => $quotation,
            // "lever_transaction" => $lever_transaction,
            "my_transaction" => $my_transaction,
            "lever_share_limit" => $lever_share_limit,
            "multiple" => LeverTransaction::leverMultiple(),
            "last_price" => $last_price,
            "user_lever" => $user_lever,
            "all_levers" => $all_levers,
            "ustd_price" => $ustd_price,
        ]);
    }

    /**
     * 交易列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealAll(Request $request)
    {
        $user_id = User::getUserId();
        $currency_match_id = $request->get("currency_match_id", 0);
        $currency_match = CurrencyMatch::where('id', $currency_match_id)->first();
        if (empty($currency_match)) {
            return $this->error(__("api.参数错误"));
        }
        $limit = $request->get("limit", 10);
        $page = $request->get("page", 1);
        $quote_currency_id = $currency_match->quote_currency_id;
        $base_currency_id = $currency_match->base_currency_id;
        $lever_transaction = LeverTransaction::with('user')
            ->orderBy('id', 'desc')
            ->where("user_id", $user_id)
            ->where("status", LeverTransaction::STATUS_TRANSACTION)
            ->where("quote_currency_id", $quote_currency_id)
            ->where("base_currency_id", $base_currency_id)
            ->paginate($limit);

        $user_wallet = LeverAccount::where('currency_id', $quote_currency_id)->where('user_id', $user_id)->first();
        $balance = $user_wallet ? $user_wallet->balance : 0;
        //取盈亏总额
        list(
            'caution_money_total' => $caution_money_all,
            'origin_caution_money_total' => $origin_caution_money_all,
            'profits_total' => $profits_all
            ) = LeverTransaction::getUserProfit($user_id, $quote_currency_id);
        //取该交易对盈亏总额
        list(
            'caution_money_total' => $caution_money,
            'origin_caution_money_total' => $origin_caution_money,
            'profits_total' => $profits
            ) = LeverTransaction::getUserProfit($user_id, $quote_currency_id, $base_currency_id);
        $total_all_money = bc($caution_money_all, '+', $balance);
        $hazard_rate = LeverTransaction::getAccountHazardRate($user_wallet);
        $data = [
            'balance' => $balance,
            'hazard_rate' => $hazard_rate,
            'caution_money_total' => $caution_money_all,
            'origin_caution_money_total' => $origin_caution_money_all,
            'profits_total' => $profits_all,
            'caution_money' => $caution_money,
            'origin_caution_money' => $origin_caution_money,
            'profits' => $profits,
            'order' => $lever_transaction,
        ];
        return $this->success('', $data);
    }

    /**
     * 我的交易
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myTrade(Request $request)
    {
        $user_id = User::getUserId();
        $currency_match_id = $request->get("currency_match_id", 0);
        $currency_match = CurrencyMatch::where('id', $currency_match_id)->first();
        if (!empty($currency_match)) {
            $quote_currency_id = $currency_match->quote_currency_id;
            $base_currency_id = $currency_match->base_currency_id;
        } else {
            $quote_currency_id = 0;
            $base_currency_id = 0;
        }

        $status = $request->get("status", -1);
        $limit = $request->get("limit", 10);
//        $param = compact('status', 'quote_currency_id', 'base_currency_id');
        $lever_transaction = LeverTransaction::where(function ($query) use (
            $status,
            $quote_currency_id,
            $base_currency_id
        ) {
            $status != -1 && $query->where('status', $status);
            $quote_currency_id > 0 && $query->where('quote_currency_id', $quote_currency_id);
            $base_currency_id > 0 && $query->where('base_currency_id', $base_currency_id);
        })->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success('', $lever_transaction);
    }

    /**
     * 提交杆杠交易
     *
     * @return void
     */
    public function submit(Request $request)
    {

        try {

            DB::beginTransaction();
            $user_id = User::getUserId();
            $share = $request->get("share");
            $multiple = $request->get("multiple");
            $type = $request->get("type", LeverTransaction::WAY_BUY);
            $currency_match_id = $request->get("currency_match_id", 0);
            $status = $request->get('status', LeverTransaction::STATUS_TRANSACTION); //默认是市价交易,为0则是挂单交易
            $target_price = $request->get('target_price', 0); //目标价格
            $currency_match = CurrencyMatch::where('id', $currency_match_id)->first();
            if (empty($currency_match) || empty($share) || empty($multiple)) {
                throw new \Exception(__("api.缺少参数或传值错误"));
            }
            $quote_currency_id = $currency_match->quote_currency_id;
            $base_currency_id = $currency_match->base_currency_id;
            $now = time();
            $user_lever = 0;

            if (!$currency_match) {
                throw new \Exception(__('api.指定交易对不存在'));
            }
            if ($currency_match->open_lever != 1) {
                throw new \Exception(__('api.您未开通本交易对的交易功能'));
            }
            //手数判断:大于0的整数,且在区间范围内
            if ($share != intval($share) || !is_numeric($share) || $share <= 0) {
                throw new \Exception(__('api.手数必须是大于0的整数'));
            }
            if (bc($currency_match->lever_min_share, '>', $share)) {
                throw new \Exception(__('api.手数不能低于') . $currency_match->lever_min_share);
            }

            if (bc($currency_match->lever_max_share, '<', $share) && bc($currency_match->lever_max_share, '>=', '0')) {
                throw new \Exception(__('api.手数不能高于') . $currency_match->lever_max_share);
            }
            //倍数判断
            $multiples = LeverMultiple::where("type", 1)->pluck('value')->all();
            if (!in_array($multiple, $multiples)) {
                throw new \Exception(__('api.选择倍数不在系统范围'));
            }
            throw_if(
                LeverTransaction::where('user_id', $user_id)->where('status', LeverTransaction::STATUS_CLOSING)->exists(),
                new \Exception(__('api.您当前有正在平仓中的交易,暂不能进行买卖'))
            );
            throw_if(
                !in_array($status, [
                    LeverTransaction::STATUS_ENTRUST,
                    LeverTransaction::STATUS_TRANSACTION
                ]),
                new \Exception(__('api.交易类型错误'))
            );
            if ($status == LeverTransaction::STATUS_ENTRUST) {
                $open_lever_entrust = Setting::getValueByKey('open_lever_entrust', 0);
                throw_if($open_lever_entrust <= 0, new \Exception(__('api.该功能暂未开放')));
            }
            //判断是否委托交易 (限价交易)
            if ($status == LeverTransaction::STATUS_ENTRUST && $target_price <= 0) {
                throw new \Exception(__('api.限价交易价格必须大于0'));
            }
            $overnight = $currency_match->lever_overnight_fee_rate ?? 0;
            //优先从行情取最新价格
            $last_price = $currency_match->getLastPrice();
            if (empty($last_price)) {
                throw new \Exception(__('api.当前没有获取到行情价格,请稍后重试'));
            }
            //挂单委托(限价交易)价格取用户设置的
            if ($status == LeverTransaction::STATUS_ENTRUST) {
                if ($type == LeverTransaction::WAY_SELL && $target_price <= $last_price) {
                    throw new \Exception(__('api.限价交易卖出不能低于当前价'));
                } elseif ($type == LeverTransaction::WAY_BUY && $target_price >= $last_price) {
                    throw new \Exception(__('api.限价交易买入价格不能高于当前价'));
                }
                $origin_price = $target_price;
            } else {
                $origin_price = $last_price;
            }
            //交易手数转换
            $lever_share_num = $currency_match->lever_share_num ?? 1;
            $num = bc($share, "*", $lever_share_num);
            //点差率
            $spread = $currency_match->lever_point_rate;
            $spread_price = bc($origin_price, '*', $spread);
            $type == LeverTransaction::WAY_SELL && $spread_price = bc(-1, '*', $spread_price); //买入应加上点差,卖出就减去点差
            $fact_price = bc($origin_price, '+', $spread_price); //收取点差之后的实际价格
            $all_money = bc($fact_price, '*', $num, 5);
            //计算手续费
            $lever_trade_fee_rate = $currency_match->lever_fee_rate;
            $trade_fee = bc($all_money, '*', $lever_trade_fee_rate);

            $legal = Account::getAccountByType(
                AccountType::LEVER_ACCOUNT_ID,
                $quote_currency_id,
                $user_id
            );
            $user_lever = $legal->balance;

            $caution_money = bc($all_money, '/', $multiple); //保证金
            $shoud_deduct = bc($caution_money, "+", $trade_fee); //保证金+手续费

            throw_if(
                bc($user_lever, '>=', $shoud_deduct) < 0,
                new \Exception(
                    __("api.下单余额不足", [
                        'quote_currency_code' => $currency_match->quote_currency_code,
                        'shoud_deduct' => $shoud_deduct,
                        'trade_fee' => $trade_fee,
                    ])
                )
            );
            $user = User::find($user_id);
            // var_dump($user->agent_path);
            // exit();
            $lever_data = [
                'user_id' => $user_id,
                'type' => $type,
                'overnight' => $overnight,
                'origin_price' => $origin_price,
                'price' => $fact_price,
                'update_price' => $last_price,
                'share' => $share,
                'number' => $num,
                'origin_caution_money' => $caution_money,
                'caution_money' => $caution_money,
                'currency_match_id' => $currency_match_id,
                'quote_currency_id' => $quote_currency_id,
                'base_currency_id' => $base_currency_id,
                'multiple' => $multiple,
                'trade_fee' => $trade_fee,
                'transaction_time' => $now,
                'create_time' => $now,
                'status' => $status,
                'agent_path' =>  $user->agent_path
            ];
            $lever_transaction = LeverTransaction::unguarded(function () use ($lever_data) {
                return LeverTransaction::create($lever_data);
            });

            $extra_data = [
                'debug_data' => [
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'multiple' => $multiple,
                ]
            ];
            $legal->changeBalance(
                AccountLog::LEVER_TRANSACTION,
                -$caution_money,
                $extra_data
            );
            $extra_data = [
                'debug_data' => serialize([
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'lever_trade_fee_rate' => $lever_trade_fee_rate,
                ])
            ];
            $legal->changeBalance(
                AccountLog::LEVER_TRANSACTION_FEE,
                -$trade_fee,
                $extra_data
            );
            DB::commit();
            event(new LeverSubmitOrderEvent($lever_transaction));
            return $this->success(__("api.提交成功"));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 设置止盈止亏
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStopPrice(Request $request)
    {
        $user_set_stopprice = Setting::getValueByKey('user_set_stopprice', 0);
        if (!$user_set_stopprice) {
            return $this->error(__('api.此功能系统未开放'));
        }
        $id = $request->get('id', 0);
        $user_id = User::getUserId();
        $target_profit_price = $request->get('target_profit_price', 0);
        $stop_loss_price = $request->get('stop_loss_price', 0);
        if ($target_profit_price <= 0 || $stop_loss_price <= 0) {
            return $this->error(__('api.止盈止损价格不能为0'));
        }
        $lever_transaction = LeverTransaction::where('user_id', $user_id)
            ->where('status', LeverTransaction::STATUS_TRANSACTION)
            ->find($id);
        if (!$lever_transaction) {
            return $this->error(__('api.The transaction could not be found'));
            // return $this->error(__('api.找不到该笔交易'));
        }
        if ($lever_transaction->type == 1) {
            //买入
            if ($target_profit_price <= $lever_transaction->price || $target_profit_price <= $lever_transaction->update_price) {
                return $this->error(__('api.买入(做多)止盈价不能低于开仓价和当前价'));
            }
            if ($stop_loss_price >= $lever_transaction->price || $stop_loss_price >= $lever_transaction->update_price) {
                return $this->error(__('api.买入(做多)止亏价不能高于开仓价和当前价'));
            }
        } else {
            //卖出
            if ($target_profit_price >= $lever_transaction->price || $target_profit_price >= $lever_transaction->update_price) {
                return $this->error(__('api.卖出(做空)止盈价不能高于开仓价和当前价'));
            }
            if ($stop_loss_price <= $lever_transaction->price || $stop_loss_price <= $lever_transaction->update_price) {
                return $this->error(__('api.卖出(做空)止亏价不能低于开仓价和当前价'));
            }
        }
        $target_profit_price > 0 && $lever_transaction->target_profit_price = $target_profit_price;
        $stop_loss_price > 0 && $lever_transaction->stop_loss_price = $stop_loss_price;
        $result = $lever_transaction->save();
        return $result ? $this->success(__('api.设置成功')) : $this->error(__('api.设置失败'));
    }

    /**
     * 平仓
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function close(Request $request)
    {
        $user_id = User::getUserId();
        $id = $request->get("id");
        if (empty($id)) {
            return $this->error(__("api.参数错误"));
        }
        DB::beginTransaction();
        try {
            $lever_transaction = LeverTransaction::lockForupdate()->find($id);
            if (empty($lever_transaction)) {
                throw new \Exception(__("api.数据未找到"));
            }
            if ($lever_transaction->user_id != $user_id) {
                throw new \Exception(__("api.无权操作"));
            }
            if ($lever_transaction->status != LeverTransaction::STATUS_TRANSACTION) {
                throw new \Exception(__("api.交易状态异常,请勿重复提交"));
            }
            $return = LeverTransaction::LeverClose($lever_transaction, LeverTransaction::CLOSED_BY_USER_SELF);
            if (!$return) {
                throw new \Exception(__("api.平仓失败,请重试"));
            }
            DB::commit();
            return $this->success(__("api.操作成功"));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 批量平仓(按买卖方向)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchCloseByType(Request $request)
    {
        $user_id = User::getUserId();
        $currency_match_id = $request->get("currency_match_id", 0);
        $currency_match = CurrencyMatch::where('id', $currency_match_id)->first();
        if (empty($currency_match)) {
            return $this->error(__("api.参数错误"));
        }
        $quote_currency_id = $currency_match->quote_currency_id;
        $base_currency_id = $currency_match->base_currency_id;
        $type = $request->input('type', 0); //0.所有,1.买入(做多),2.卖出(做空)
        if (!in_array($type, [0, 1, 2])) {
            return $this->error(__('api.买入方向传参错误'));
        }
        $lever = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
            ->where('user_id', $user_id)
            ->where(function ($query) use ($type, $base_currency_id, $quote_currency_id) {
                !empty($quote_currency_id) && $query->where('quote_currency_id', $quote_currency_id);
                !empty($base_currency_id) && $query->where('base_currency_id', $base_currency_id);
                !empty($type) && $query->where('type', $type);
            })->get();
        $task_list = $lever->pluck('id')->all();
        $result = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
            ->whereIn('id', $task_list)
            ->update([
                'closed_type' => 1,
                'status' => LeverTransaction::STATUS_CLOSING,
                'handle_time' => microtime(true),
            ]);
        if ($result > 0) {
            LeverClosing::dispatch($task_list, true)->onQueue('lever:close');
        }
        return $result > 0 ? $this->success(__('api.提交成功,请等待系统处理')) : $this->error(__('api.未找到需要平仓的交易'));
    }

    /**
     * 批量平仓(按盈亏)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchCloseByProfit(Request $request)
    {
        $user_id = User::getUserId();
        $type = $request->input('type'); //0.所有,1.盈,2.亏
        $lever = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
            ->where('user_id', $user_id)
            ->get();
        switch ($type) {
            case 1:
                $lever = $lever->where('profits', '>', 0);
                break;
            case 2:
                $lever = $lever->where('profits', '<', 0);
                break;
            default:
        }
        $task_list = $lever->pluck('id')->all();
        $result = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
            ->whereIn('id', $task_list)
            ->update([
                'closed_type' => 1,
                'status' => LeverTransaction::STATUS_CLOSING,
                'handle_time' => microtime(true),
            ]);
        if ($result > 0) {
            LeverClosing::dispatch($task_list, true)->onQueue('lever:close');
        }
        return $result > 0 ? $this->success(__('api.提交成功,请等待系统处理')) : $this->error(__('api.未找到需要平仓的交易'));
    }

    /**
     * 取最近几条撮合交易
     *
     * @param integer $legal_id    法币id
     * @param integer $currency_id 交易币id
     * @param integer $limit       限制条数,默认5
     *
     * @return array
     */
    public function getLastMathTransaction($legal_id, $currency_id, $limit = 5)
    {
        $in = TxIn::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'desc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit($limit)
            ->get();
        $out = TxOut::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'asc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit($limit)
            ->get()
            ->sortByDesc('price')
            ->values();
        return [
            'in' => $in,
            'out' => $out,
        ];
    }

    /**
     * 取最近几条合约交易
     *
     * @param integer $quote_currency_id 计价币id
     * @param integer $base_currency_id  交易币id
     * @param integer $limit             限制条数,默认5
     *
     * @return array
     */
    public function getLastLeverTransaction($quote_currency_id, $base_currency_id, $limit = 5)
    {
        $in = LeverTransaction::with('user')
            ->where('quote_currency_id', $quote_currency_id)
            ->where('base_currency_id', $base_currency_id)
            ->where('type', LeverTransaction::WAY_BUY)
            ->where('status', LeverTransaction::STATUS_TRANSACTION)
            ->orderBy('price', 'desc')
            ->limit($limit)
            ->get();
        $out = LeverTransaction::with('user')
            ->where('quote_currency_id', $quote_currency_id)
            ->where('base_currency_id', $base_currency_id)
            ->where('type', LeverTransaction::WAY_SELL)
            ->where('status', LeverTransaction::STATUS_TRANSACTION)
            ->orderBy('price', 'asc')
            ->limit($limit)
            ->get()
            ->sortByDesc('price')
            ->values();
        return [
            'in' => $in,
            'out' => $out,
        ];
    }

    /**
     * 取消挂单(撤单)
     *
     * @return boolean
     */
    public function cancelTrade(Request $request)
    {
        $user_id = User::getUserId();
        $id = $request->input('id');
        try {
            //退手续费和保证金
            DB::transaction(function () use ($user_id, $id) {
                $lever_trade = LeverTransaction::where('user_id', $user_id)
                    ->where('status', LeverTransaction::STATUS_ENTRUST)
                    ->lockForUpdate()
                    ->find($id);
                if (!$lever_trade) {
                    throw new \Exception(__('api.交易不存在或已撤单,请刷新后重试'));
                }
                $quote_currency_id = $lever_trade->quote_currency_id;
                $refund_trade_fee = $lever_trade->trade_fee;
                $refund_caution_money = $lever_trade->caution_money;

                $legal_wallet = Account::getAccountByType(AccountType::LEVER_ACCOUNT_ID, $quote_currency_id, $user_id);
                if (!$legal_wallet) {
                    throw new \Exception(__('api.撤单失败:用户钱包不存在'));
                }
                $data = ['strict' => false];
                $legal_wallet->changeBalance(AccountLog::LEVER_TRANSACTION_FEE_CANCEL,
                    $refund_trade_fee, $data);
                $data1 = [ 'strict' => false];

                $legal_wallet->changeBalance(AccountLog::LEVER_TRANSACTION_CANCEL,
                    $refund_caution_money, $data1);
                $lever_trade->status = LeverTransaction::STATUS_CANCEL;
                $lever_trade->complete_time = time();
                $result = $lever_trade->save();
                if (!$result) {
                    throw new \Exception(__('api.撤单失败:变更状态失败'));
                }
                $lever_trades = collect([$lever_trade]);
                LeverTransaction::pushDeletedTrade($lever_trades);
            });
            return $this->success(__('api.撤单成功'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    /**请求系统内的所有交易对
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function currencyLeverMatches()
    {
        $currencyMatches = Currency::with('matches.currencyQuotation')
            ->where('is_quote', Currency::ON)
            ->whereHas('matches.currencyQuotation', function ($query) {
                $query->where("open_lever", 1);
            })->get();
        $data['match'] = $currencyMatches;
        $data['share'] = LeverMultiple::where('type', 2)->get();
        $data['multiple'] = LeverMultiple::where('type', 1)->get();
        return $this->success(__('api.请求成功'), $data);
    }
}
