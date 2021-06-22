<?php

namespace App\Models\Otc;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Model;
use App\Models\Account\{AccountLog, LegalAccount};
use App\Models\Currency\{Currency};
use App\Models\Setting\Setting;
use App\Models\User\{User, UserPayway};
use InvalidArgumentException;

class OtcDetail extends Model
{
    /*
    |--------------------------------------------------------------------------
    | 常量定义
    |--------------------------------------------------------------------------
    |
    */

    // 交易状态
    /** @var string 交易中 */
    const STATUS_OPENED = 0;
    /** @var string 已付款 */
    const STATUS_PAYED = 1;
    /** @var string 延期确认  */
    const STATUS_DEFERRED = 2;
    /** @var string 维权 */
    const STATUS_ARBITRATED = 3;
    /** @var string 取消 */
    const STATUS_CANCELED = 4;
    /** @var string 确认 */
    const STATUS_CONFIRMED = 5;

    // 交易方向
    /** @var string 买入 */
    const WAY_BUY = 'BUY';
    /** @var string 卖出 */
    const WAY_SELL = 'SELL';

    protected $attributes = [
        'status' => self::STATUS_OPENED,
    ];

    protected $dates = [
        'canceled_at',
        'payed_at',
        'defered_at',
        'arbitrated_at',
        'finished_at',
    ];

    protected $appends = [
        'buy_user_account',
        'sell_user_account',
        'currency_name',
        'sell_user_payways',
        'area_info',
        'pay_countdown',
        'confirm_countdown',
        'server_now',
    ];

    /*
    |--------------------------------------------------------------------------
    | 定义模型关联
    |--------------------------------------------------------------------------
    |
    */

    public function master()
    {
        return $this->belongsTo(OtcMaster::class, 'master_id', 'id');
    }

    public function buyUser()
    {
        return $this->belongsTo(User::class, 'buy_user_id', 'id');
    }

    public function sellUser()
    {
        return $this->belongsTo(User::class, 'sell_user_id', 'id');
    }

    public function actions()
    {
        return $this->hasMany(OtcAction::class, 'detail_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

    public function otcPayways()
    {
        return $this->hasMany(OtcPayway::class, 'detail_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | 访问器
    |--------------------------------------------------------------------------
    |
    */
    public function getCurrencyNameAttribute()
    {
        return $this->currency->code ?? __('model.未知');
    }

    public function getBuyUserAccountAttribute()
    {
        return $this->buyUser->account ?? __('model.未知');
    }

    public function getSellUserAccountAttribute()
    {
        return $this->sellUser->account ?? __('model.未知');
    }

    public function getSellUserPaywaysAttribute()
    {
        return $this->otcPayways ?? [];
    }

    public function getAmountAttribute($value)
    {
        return bc($value, '<=', 0) ? bc($this->attributes['price'], '*', $this->attributes['number']): $value;
    }

    public function getPayCountdownAttribute()
    {
        $status = $this->attributes['status'];
        if ($status == self::STATUS_OPENED) {
            $otc_pay_timeout = self::getOtcTimeout('otc_pay_timeout');
            return $otc_pay_timeout > 0 ? $this->created_at->addMinutes($otc_pay_timeout)->format($this->getDateFormat()) : null;
        } else {
            return null;
        }
    }

    public function getConfirmCountdownAttribute()
    {
        $status = $this->attributes['status'];
        $otc_confrim_timeout = self::getOtcTimeout('otc_confrim_timeout');
        $otc_defer_timeout = self::getOtcTimeout('otc_defer_timeout');
        if ($status == self::STATUS_PAYED) {
            return $otc_confrim_timeout > 0 ? $this->payed_at->addMinutes($otc_confrim_timeout)->format($this->getDateFormat()) : null;
        } elseif ($status == self::STATUS_DEFERRED) {
            return $otc_defer_timeout > 0 ? $this->defered_at->addMinutes($otc_defer_timeout)->format($this->getDateFormat()) : null;
        } else {
            return null;
        }
    }

    public function getServerNowAttribute()
    {
        return Carbon::now()->format($this->getDateFormat());
    }

    public function getAreaInfoAttribute()
    {
        return $this->master->area ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | 相关方法封装
    |--------------------------------------------------------------------------
    |
    */
    /**
     * 获取法币交易的超时时间
     *
     * @param string|null $key
     * @return array|string|integer
     */
    public static function getOtcTimeout($key = null)
    {
        if (Cache::has("otc_timeout_params")) {
            $otc_timeout_params = Cache::get("otc_timeout_params");
        } else {
            $otc_timeout_params = [
                'otc_pay_timeout' => Setting::getValueByKey('otc_pay_timeout', 0),
                'otc_confrim_timeout' => Setting::getValueByKey('otc_confrim_timeout', 0),
                'otc_defer_timeout' => Setting::getValueByKey('otc_defer_timeout', 0),
            ];
            Cache::put("otc_timeout_params", $otc_timeout_params, Carbon::now()->addDays(1));
        }
        if (is_null($key)) {
            return $otc_timeout_params;
        } else {
            return array_key_exists($key, $otc_timeout_params) ? $otc_timeout_params[$key]: 0;
        }
    }

    /**
     * 检测用户是否存在有纠纷的交易
     *
     * @param integer $user_id
     * @return boolean
     */
    public static function checkUserDispute($user_id)
    {
        return OtcDetail::where('status', self::STATUS_ARBITRATED)->where(function ($query) use ($user_id) {
                $query->where('user_id', $user_id)->orWhere('buy_user_id', $user_id)->orWhere('sell_user_id', $user_id);
            })->exists();
    }

    /**
     * 取超时未支付的交易订单
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUnpayJobs()
    {
        $now = Carbon::now();
        $otc_pay_timeout = OtcDetail::getOtcTimeout('otc_pay_timeout');
        $otc_details = OtcDetail::where('status', OtcDetail::STATUS_OPENED)
            ->where('created_at', '<=', $now->subMinutes($otc_pay_timeout))
            ->get();
        return $otc_details;
    }

    /**
     * 取超时未确认的交易订单
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUnConfrimJobs()
    {
        $now = Carbon::now();
        $otc_confrim_timeout = OtcDetail::getOtcTimeout('otc_confrim_timeout');
        $otc_details = OtcDetail::where('status', OtcDetail::STATUS_PAYED)
            ->where('payed_at', '<=', $now->subMinutes($otc_confrim_timeout))
            ->get();
        return $otc_details;
    }

    /**
     * 取延期后超时确认的交易订单
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getDeferedUnConfrimJobs()
    {
        $now = Carbon::now();
        $otc_defer_timeout = OtcDetail::getOtcTimeout('otc_defer_timeout');
        $otc_details = OtcDetail::where('status', OtcDetail::STATUS_DEFERRED)
            ->where('defered_at', '<=', $now->subMinutes($otc_defer_timeout))
            ->get();
        return $otc_details;
    }

    /**
     * 匹配挂单
     *
     * @param \App\Models\Otc\OtcMaster $otc_master
     * @param \App\Models\User\User $user
     * @param float $number
     * @return \App\Models\Otc\OtcDetail
     */
    public static function matchMaster($otc_master, $user, $number)
    {
        try {
            return DB::transaction(function () use ($otc_master, $user, $number) {
                $otc_master = $otc_master->lockForUpdate()->find($otc_master->getKey());
                throw_if($otc_master->status != OtcMaster::STATUS_OPENED, new \Exception(__("model.商家挂单当前状态异常")));
                if ($otc_master->way == OtcMaster::WAY_BUY) {
                    $way = self::WAY_SELL;
                    $buy_user_id = $otc_master->user_id;
                    $sell_user_id = $user->id;
                    // 检测卖方是否设置了收款信息
                    $not_set_payway = UserPayway::where('user_id', $user->id)->doesntExist();
                    throw_if($not_set_payway, new \Exception(__('model.请先设置收款/支付信息')));
                    // 检测买卖双方收付款信息是否匹配
                    throw_unless(
                        UserPayway::where('user_id', $user->id)->whereIn('payway_id', $otc_master->payways)->exists(),
                        new \Exception(__("model.您的收款方式与商家发布挂单付款方式不匹配,不能交易"))
                    );
                    // 扣除卖方资金
                    $account = LegalAccount::getAccountForLock($otc_master->currency_id, $user->id);
                    $balance = $account->balance;
                    if (bc($number, '>', $account->balance)) {
                        throw new \Exception(__('otc.抱歉,您的:balance_type余额不足! 当前余额::balance', [
                            'balance_type' => __("otc.balance"),
                            'balance' => $balance,
                        ]));
                    }
                    $account->changeBalance(
                        AccountLog::LEGAL_USER_SELL_MATCH,
                        -$number,
                        ['memo' => __('otc.用户匹配交易销售')]
                    );
                    $account->changeLockBalance(
                        AccountLog::LEGAL_USER_SELL_MATCH,
                        $number,
                        ['memo' => __('otc.用户匹配交易销售')]
                    );
                } elseif ($otc_master->way == OtcMaster::WAY_SELL) {
                    $way = self::WAY_BUY;
                    $buy_user_id = $user->id;
                    $sell_user_id = $otc_master->user_id;
                } else {
                    throw new \Exception(__('model.商家订单方向异常'));
                }
                // 判断剩余数量是否允足,并减少挂单的剩余数量
                $otc_master->remain_qty = bc($otc_master->remain_qty, '-', $number); // 减少剩余数量
                throw_if(bc($otc_master->remain_qty, '<', 0), new \Exception(__('model.抱歉,您手慢了,剩余可交易数量不足')));
                $otc_master->dealing_qty = bc($otc_master->dealing_qty, '+', $number); // 增加交易中数量
                throw_unless($otc_master->save(), new \Exception(__('model.匹配交易失败')));
                $otc_detail = self::create([
                    'master_id' => $otc_master->id,
                    'currency_id' => $otc_master->currency_id,
                    'way' => $way,
                    'seller_id' => $otc_master->seller->id,
                    'user_id' => $user->id,
                    'seller_user_id' => $otc_master->seller->user_id,
                    'buy_user_id' => $buy_user_id,
                    'sell_user_id' => $sell_user_id,
                    'number' => $number,
                    'price' => $otc_master->price,
                    'amount' => bc($otc_master->price, '*', $number),
                ]);
                // 写入卖方的支付信息
                $user_payways = $otc_detail->sellUser->UserPayways;
                $otc_payway_data = [];
                $master_payways = $otc_master->payways->pluck('id')->all();
                $user_payways->each(function ($item) use (&$otc_payway_data, $master_payways) {
                    if (in_array($item->payway_id, $master_payways)) {
                        $otc_payway_data[] = new OtcPayway([
                            'user_id' => $item->user_id,
                            'payway_id' => $item->payway_id,
                            'raw_data' => $item->raw_data,
                        ]);
                    }
                });
                $otc_detail->otcPayways()->saveMany($otc_payway_data);
                // 更新交易的状态追踪记录
                OtcAction::create([
                    'detail_id' => $otc_detail->id,
                    'user_id' => $user->id,
                    'buy_user_id' => $buy_user_id,
                    'sell_user_id' => $sell_user_id,
                    'status' => self::STATUS_OPENED, // 交易匹配成功,开始交易
                    'public_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '',
                    ],
                    'to_buy_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '交易已匹配,请您在30分钟内完成付款并上传支付凭证,否则交易将自动取消',
                    ],
                    'to_sell_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '交易已匹配,请等待买方付款,超过30分钟将自动取消',
                    ],
                    'memo' => '配对交易',
                ]);
                return $otc_detail;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 取消交易
     *
     * @param \App\Models\Otc\OtcDetail $otc_detail
     * @param \App\Models\Otc\User|\App\Models\Admin\Admin|null $user
     * @param integer $operator_type 操作员类型
     * @return \App\Models\Otc\OtcDetail
     */
    public static function cancel($otc_detail, $user, $operator_type = OtcMaster::OPERATOR_USER)
    {
        try {
            return DB::transaction(function () use ($otc_detail, $user, $operator_type) {
                $otc_detail = OtcDetail::lockForUpdate()->findOrFail($otc_detail->getKey());
                throw_if(!in_array($otc_detail->status, [
                    self::STATUS_OPENED,
                    self::STATUS_DEFERRED,
                    self::STATUS_ARBITRATED,
                ]), new \Exception(__('model.当前状态不能取消')));
                $operator_map = OtcMaster::getOperatorMap();
                $operator_name = $operator_map[$operator_type];
                $otc_detail->fill([
                    'status' => self::STATUS_CANCELED,
                    'canceled_at' => Carbon::now(),
                    'finished_at' => Carbon::now(),
                ])->save();
                // 取消交易需要退回原商家发币挂单交易时的剩余交易额度
                /** @var \App\Models\Otc\OtcMaster $otc_master */
                $otc_master = $otc_detail->master->lockForUpdate()->find($otc_detail->master->getKey());
                $otc_master->dealing_qty = bc($otc_master->dealing_qty, '-', $otc_detail->number); // 减少交易中的数量
                throw_if(bc($otc_master->dealing_qty, '<', 0), new \Exception(__("nodel.取消失败,商家挂单对应交易中数量不足")));
                $otc_master->remain_qty = bc($otc_master->remain_qty, '+', $otc_detail->number); // 增加剩余数量
                throw_unless($otc_master->save(), new \Exception(__('model.同步商家挂单交易信息失败')));
                // 退回卖方(仅用户,不含商家)的冻结资金
                if ($otc_detail->way == self::WAY_SELL) {
                    $sell_account = LegalAccount::getAccountForLock($otc_master->currency_id, $otc_detail->sell_user_id);
                    $sell_account->changeLockBalance(
                        AccountLog::LEGAL_DETAIL_CANCEL,
                        -$otc_detail->number,
                        ['memo' => __('otc.法币交易:取消交易')]
                    );
                    $sell_account->changeBalance(
                        AccountLog::LEGAL_DETAIL_CANCEL,
                        $otc_detail->number,
                        ['memo' => __('otc.法币交易:取消交易')]
                    );
                }
                // 更新交易的状态追踪记录
                OtcAction::create([
                    'detail_id' => $otc_detail->id,
                    'user_id' => $user->id ?? 0, // 取消的极有可能是后台系统管理员，也有可能是超时自动取消
                    'buy_user_id' => $otc_detail->buy_user_id,
                    'sell_user_id' => $otc_detail->sell_user_id,
                    'status' => self::STATUS_CANCELED, // 交易匹配成功,开始交易
                    'operator_type' => $operator_type,
                    'public_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '',
                    ],
                    'to_buy_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => "交易已被{$operator_name}取消",
                    ],
                    'to_sell_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => "交易已被{$operator_name}取消",
                    ],
                    'memo' => '法币交易:取消交易',
                ]);

                return $otc_detail;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 交易支付
     *
     * @param \App\Models\Otc\OtcDetail $otc_detail
     * @param \App\Models\User\User $user
     * @param string $pay_voucher
     * @return \App\Models\Otc\OtcDetail
     */
    public static function pay($otc_detail, $user, $pay_voucher)
    {
        try {
            return DB::transaction(function () use ($user, $otc_detail, $pay_voucher) {
                // 重新查询并锁定交易
                $otc_detail = $otc_detail->lockForUpdate()->find($otc_detail->getKey());
                throw_if($otc_detail->status != self::STATUS_OPENED, new \Exception(__("model.当前状态不能支付")));
                $otc_detail->fill([
                    'pay_voucher' => $pay_voucher,
                    'status' => self::STATUS_PAYED,
                    'payed_at' => Carbon::now(),
                ])->save();
                // 更新交易的状态追踪记录
                OtcAction::create([
                    'detail_id' => $otc_detail->id,
                    'user_id' => $user->id ?? 0,
                    'buy_user_id' => $otc_detail->buy_user_id,
                    'sell_user_id' => $otc_detail->sell_user_id,
                    'status' => self::STATUS_PAYED, // 交易已支付
                    'public_msg' => [
                        'type' => 'image',
                        'title' => '',
                        'content' => $pay_voucher,
                    ],
                    'to_buy_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '您已支付,请等待卖方确认收款,超过30分钟将自动确认',
                    ],
                    'to_sell_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '买方已支付,请您核实支付凭证无误后确认收款,超过30分钟系统将自动向买方确认,如临期仍未收到付款请您提前申请延期!',
                    ],
                    'memo' => '买方已支付',
                ]);
                return $otc_detail;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 交易确认
     *
     * @param \App\Models\Otc\OtcDetail $otc_detail
     * @param \App\Models\Otc\User|\App\Models\Admin\Admin|null $user
     * @return \App\Models\Otc\OtcDetail
     */
    public static function confirm($otc_detail, $user, $operator_type = OtcMaster::OPERATOR_USER)
    {
        try {
            return DB::transaction(function () use ($otc_detail, $user, $operator_type) {
                throw_if(!in_array($otc_detail->status, [
                    OtcDetail::STATUS_PAYED,
                    OtcDetail::STATUS_DEFERRED,
                    OtcDetail::STATUS_ARBITRATED
                ]), new \Exception(__("model.当前状态不能确认")));
                $operator_map = OtcMaster::getOperatorMap();
                $operator_name = $operator_map[$operator_type];
                // 更改交易状态
                $otc_detail = $otc_detail->lockForUpdate()->find($otc_detail->getKey());
                $otc_detail->fill([
                    'status' => self::STATUS_CONFIRMED,
                    'finished_at' => Carbon::now(),
                ])->save();
                // 更新交易的状态追踪记录
                $otc_action = OtcAction::create([
                    'detail_id' => $otc_detail->id,
                    'user_id' => $user->id ?? 0,
                    'buy_user_id' => $otc_detail->buy_user_id,
                    'sell_user_id' => $otc_detail->sell_user_id,
                    'status' => self::STATUS_CONFIRMED, // 交易已确认
                    'operator_type' => $operator_type,
                    'public_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '',
                    ],
                    'to_buy_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => "{$operator_name}确认收款,交易成功!",
                    ],
                    'to_sell_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => "{$operator_name}确认收款,交易成功!",
                    ],
                    'memo' => '交易确认',
                ]);
                // 扣除卖家冻结法币,给买家到账
                $sell_account = LegalAccount::getAccountForLock($otc_detail->currency_id, $otc_detail->sell_user_id);
                if (bc($sell_account->lock_balance, '<', $otc_detail->number)) {
                    throw new \Exception(__('model.确认失败,卖方法币冻结余额不足'));
                }
                $sell_account->changeLockBalance(
                    AccountLog::LEGAL_DETAIL_CONFIRM,
                    -$otc_detail->number,
                    ['memo' => __('otc.卖出确认交易')]
                );
                $buy_wallet = LegalAccount::getAccountForLock($otc_detail->currency_id, $otc_detail->buy_user_id);
                $buy_wallet->changeBalance(
                    AccountLog::LEGAL_DETAIL_CONFIRM,
                    $otc_detail->number,
                    ['memo' => __('otc.卖出确认交易')]
                );
                /** @var \App\Models\Otc\OtcMaster $otc_master */
                $otc_master = $otc_detail->master->lockForUpdate()->find($otc_detail->master->getKey());
                $otc_master->completed_qty = bc($otc_master->completed_qty, '+', $otc_detail->number);
                $otc_master->dealing_qty = bc($otc_master->dealing_qty, '-', $otc_detail->number);
                // 检测是否是最后一笔交易,如果是则应把商家挂单标记为完成
                if (
                    $otc_master->remain_qty == 0 // 剩余数量为0
                    && !$otc_master->hasDealDetail() && $otc_master->dealing_qty == 0 // 交易中的数量为0
                    && $otc_master->total_qty == $otc_master->completed_qty
                ) {
                    // 剩余数量为0并且当前没有未完成的交易就可视为交易完成
                    $otc_master->finished_at = Carbon::now();
                    $otc_master->status = OtcMaster::STATUS_FINISHED;
                }
               
                $otc_master->save();
                return $otc_detail;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 交易延期
     *
     * @param \App\Models\Otc\OtcDetail $otc_detail
     * @param \App\Models\User\User $user
     * @return \App\Models\Otc\OtcDetail
     */
    public static function defer($otc_detail, $user)
    {
        try {
            return DB::transaction(function () use ($otc_detail, $user) {
                // 更改交易状态
                $otc_detail = $otc_detail->lockForUpdate()->find($otc_detail->getKey());
                throw_if($otc_detail->status != self::STATUS_PAYED, new \Exception(__("model.当前不能申请延期")));
                $otc_detail->fill([
                    'status' => self::STATUS_DEFERRED,
                    'defered_at' => Carbon::now(),
                ])->save();
                // 更新交易的状态追踪记录
                $otc_action = OtcAction::create([
                    'detail_id' => $otc_detail->id,
                    'user_id' => $user->id,
                    'buy_user_id' => $otc_detail->buy_user_id,
                    'sell_user_id' => $otc_detail->sell_user_id,
                    'status' => self::STATUS_DEFERRED, // 交易申请延期确认
                    'public_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '',
                    ],
                    'to_buy_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => "卖方暂未收到付款,申请交易延期确认,交易确认时间将延长30分钟,请您耐心等待!",
                    ],
                    'to_sell_msg' => [
                        'type' => 'text',
                        'content' => "您已申请交易延期确认,交易确认时间将延长30分钟!如临期仍未收到付款请您提前申请维权,否则超时后系统将自动向买方确认收款!",
                    ],
                    'memo' => '交易确认',
                ]);
                return $otc_detail;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 申请交易维权
     *
     * @param \App\Models\Otc\OtcDetail $otc_detail
     * @param \App\Models\Otc\User $user
     * @return \App\Models\Otc\OtcDetail
     */
    public static function arbitrate($otc_detail, $user)
    {
        try {
            return DB::transaction(function () use ($otc_detail, $user) {
                // 更改交易状态
                $otc_detail = $otc_detail->lockForUpdate()->find($otc_detail->getKey());
                throw_if($otc_detail->status != self::STATUS_DEFERRED, new \Exception("当前状态不能发起维权"));
                $otc_detail->fill([
                    'status' => self::STATUS_ARBITRATED,
                    'arbitrated_at' => Carbon::now(),
                ])->save();
                // 更新交易的状态追踪记录
                $otc_action = OtcAction::create([
                    'detail_id' => $otc_detail->id,
                    'user_id' => $user->id,
                    'buy_user_id' => $otc_detail->buy_user_id,
                    'sell_user_id' => $otc_detail->sell_user_id,
                    'status' => self::STATUS_ARBITRATED, // 交易进入维权
                    'public_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '',
                    ],
                    'to_buy_msg' => [
                        'type' => 'text',
                        'content' => '卖方延其内仍未收到付款,交易转交系统仲裁,您可以继续在交易中提交相关凭证以证明您已支付并且支付信息真实有效',
                    ],
                    'to_sell_msg' => [
                        'type' => 'text',
                        'title' => '',
                        'content' => '您已申请交易维权,请等待系统处理,您可以继续在交易中提交相关凭证以证明您的确没有收到付款',
                    ],
                    'memo' => '交易确认',
                ]);
                return $otc_detail;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
