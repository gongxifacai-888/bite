<?php

namespace App\Models\Otc;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\System\Payway;
use App\Models\Account\{AccountLog, LegalAccount};
use App\Models\Currency\Currency;
use App\Models\Otc\Seller;
use App\Models\User\User;
use App\Models\Model;
use App\Models\System\Area;

class OtcMaster extends Model
{
    /*
    |--------------------------------------------------------------------------
    | 常量定义
    |--------------------------------------------------------------------------
    |
    */

    // 挂单状态
    /** @var string 暂停 */
    const STATUS_PAUSED = 0;
    /** @var string 开放 */
    const STATUS_OPENED = 1;
    /** @var string 下架 */
    const STATUS_STOPPED = 2;
    /** @var string 完成 */
    const STATUS_FINISHED = 3;
    /** @var string 取消 */
    const STATUS_CANCELED = 4;

    // 挂单方向
    /** @var string 挂买 */
    const WAY_BUY = 'BUY';
    /** @var string 挂卖 */
    const WAY_SELL = 'SELL';

    //  操作员类型
    /** @var string 用户 */
    const OPERATOR_USER = 0;
    /** @var string 系统 */
    const OPERATOR_SYSTEM = 1;
    /** @var string 后台管理员 */
    const OPERATOR_ADMIN = 2;

    protected static $operatorMap = [
        self::OPERATOR_USER => '用户',
        self::OPERATOR_SYSTEM => '系统',
        self::OPERATOR_ADMIN => '管理员',
    ];

    protected $attributes = [
        'completed_qty' => 0,
        'dealing_qty' => 0,
        'status' => 0,
    ];

    protected $dates = [
        'started_at',
        'paused_at',
        'stoped_at',
    ];

    protected $appends = [
        'user_uid',
        'seller_name',
        'seller_uid',
        'currency_name',
        'area_currency',
        'status_name',
        'payways_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | 定义模型关联
    |--------------------------------------------------------------------------
    |
    */
    public function details()
    {
        return $this->hasMany(OtcDetail::class, 'master_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | 访问器
    |--------------------------------------------------------------------------
    |
    */
    public function getCurrencyNameAttribute()
    {
        return $this->currency->code ?? '';
    }

    public function getUserUidAttribute()
    {
        return $this->user->uid ?? '';
    }

    public function getSellerAccountAttribute()
    {
        return $this->seller->user->uid ?? '';
    }

    public function getSellerUidAttribute()
    {
        return $this->seller->uid ?? '';
    }

    public function getSellerNameAttribute()
    {
        return $this->seller->name ?? '';
    }

    public function getAreaCurrencyAttribute()
    {
        return $this->area->currency ?? '';
    }

    public function getPaywaysAttribute($value)
    {
        $payways_array = array_filter(explode(',', $value));
        $payways = Payway::whereIn('id', $payways_array)->get();
        return $payways;
    }

    public function getPaywaysNameAttribute()
    {
        $payways = explode(",", $this->attributes['payways']);
        $collection = Payway::whereIn("id", $payways)->get();
        $name = "";
        foreach ($collection as $pay) {
            $name .= "$pay->name ,";
        }
        return trim($name, ",");
    }


    public function getStatusNameAttribute()
    {
        $status = $this->attributes['status'];
        $name = [
            self::STATUS_PAUSED => __('model.暂停'),
            self::STATUS_OPENED => __('model.开放'),
            self::STATUS_STOPPED => __('model.下架'),
            self::STATUS_FINISHED => __('model.完成'),
            self::STATUS_CANCELED => __('model.取消'),
        ];
        return array_key_exists($status,  $name) ? $name[$status] : __('model.未知');
    }


    /*
    |--------------------------------------------------------------------------
    | 修改器
    |--------------------------------------------------------------------------
    |
    */
    public function setTotalQtyAttribute($value)
    {
        $this->attributes['total_qty'] = $value; // 总数量
        $completed_qty = $this->attributes['completed_qty']; // 已完成数量
        $dealing_qty = $this->attributes['dealing_qty']; // 交易中数量
        $remain_qty = bc($value, '-', $completed_qty); // 剩余数量
        $this->attributes['remain_qty'] = bc($remain_qty, '-', $dealing_qty);
    }

    public function setPaywaysAttribute($value)
    {
        $this->attributes['payways'] = implode(',', $value);
    }

    /*
    |--------------------------------------------------------------------------
    | 相关方法封装
    |--------------------------------------------------------------------------
    |
    */

    /**
     * 取操作员类型
     *
     * @return array
     */
    public static function getOperatorMap()
    {
        return self::$operatorMap;
    }
    /**
     * 是否有未完成的交易
     *
     * @return boolean
     */
    public function hasDealDetail()
    {
        // 查询当前有无未完成的交易
        return $this->details()->whereNotIn('status', [
            OtcDetail::STATUS_CANCELED,
            OtcDetail::STATUS_CONFIRMED,
        ])->exists();
    }

    /**
     * 发布挂单
     *
     * @param \App\Models\User $user_id
     * @param array $data
     * @return \App\Models\OtcMaster
     * @throws \Exception
     */
    public static function postMaster($user, $data)
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                $seller = $user->seller;
                extract($data);
                if ($way == self::WAY_SELL) {
                    // 扣除对应资金
                    $account = LegalAccount::getAccountForLock($currency_id, $user->id);
                    throw_if(!$account, new \Exception(__("model.用户该币种法币账户不存在")));
                    $balance = $account->balance;
                    if (bc($total_qty, '>', $account->balance)) {
                        throw new \Exception(__('otc.抱歉,您的:balance_type余额不足! 当前余额::balance', [
                            'balance_type' => __("otc.法币账户"),
                            'balance' => $balance,
                        ]));
                    }
                    $account->changeBalance(
                        AccountLog::LEGAL_POST_DEAL,
                        -$total_qty,
                        ['memo' => __('otc.商家发布挂卖交易')]
                    );
                    $account->changeLockBalance(
                        AccountLog::LEGAL_POST_DEAL,
                        $total_qty,
                        ['memo' => __('otc.商家发布挂卖交易')]
                    );
                }
                $data = array_merge($data, [
                    'seller_id' => $seller->id,
                    'user_id' => $user->id,
                ]);
                // 生成交易挂单
                $otc_master = OtcMaster::create($data);
                return $otc_master;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 开放挂单交易
     *
     * @param self $otc_master
     * @param \App\Models\Otc\User|\App\Models\Admin\Admin|null $user
     * @param string|int $operator_type
     * @return self
     */
    public static function startMaster($otc_master, $user = null, $operator_type = OtcMaster::OPERATOR_USER)
    {
        try {
            return DB::transaction(function () use ($otc_master, $user, $operator_type) {
                $otc_master = $otc_master->lockForUpdate()->findOrFail($otc_master->getKey());
                throw_if($otc_master->status != self::STATUS_PAUSED, new \Exception(__('model.当前状态不能开启挂单交易')));
                $otc_master->status = self::STATUS_OPENED;
                $otc_master->started_at = Carbon::now();
                $otc_master->save();
                // 生成日志
                $operator_map = OtcMaster::getOperatorMap();
                $operator_name = $operator_map[$operator_type];
                OtcMasterLog::create([
                    'master_id' => $otc_master->id,
                    'user_id' => $user->id ?? 0,
                    'operator_type' => $operator_type,
                    'status' => self::STATUS_OPENED,
                    'memo' => __("model.开启挂单交易", ['operator_type' => __("model.{$operator_name}")]),
                ]);
                return $otc_master;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 暂停挂单交易
     *
     * @param self $otc_master
     * @param \App\Models\Otc\User|\App\Models\Admin\Admin|null $user
     * @param string|int $operator_type
     * @return self
     */
    public static function pauseMaster($otc_master, $user = null, $operator_type = OtcMaster::OPERATOR_USER)
    {
        try {
            return DB::transaction(function () use ($otc_master, $user, $operator_type) {
                $otc_master = $otc_master->lockForUpdate()->findOrFail($otc_master->getKey());
                $allow_operate_status = [self::STATUS_OPENED];
                // 管理员可以在挂单下架后恢复为暂停,再由商家决定是否开放交易
                if ($operator_type == OtcMaster::OPERATOR_ADMIN) {
                    $allow_operate_status[] = self::STATUS_STOPPED;
                }
                throw_if(
                    !in_array($otc_master->status, $allow_operate_status),
                    new \Exception(__('model.当前状态不能暂停挂单交易'))
                );
                $otc_master->status = self::STATUS_PAUSED;
                $otc_master->paused_at = Carbon::now();
                $otc_master->save();
                // 生成日志
                $operator_map = OtcMaster::getOperatorMap();
                $operator_name = $operator_map[$operator_type];
                OtcMasterLog::create([
                    'master_id' => $otc_master->id,
                    'user_id' => $user->id ?? 0,
                    'operator_type' => $operator_type,
                    'status' => self::STATUS_PAUSED,
                    'memo' => __("model.暂停挂单交易", ['operator_type' => __("model.{$operator_name}")]),
                ]);
                return $otc_master;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 停止/下架交易(后台,下架后商家不能自行上架,须后台处理)
     *
     * @param self $otc_master
     * @param \App\Models\Otc\User|\App\Models\Admin\Admin|null $user
     * @param string|int $operator_type
     * @return self
     */
    public static function stopMaster($otc_master, $user = null, $operator_type = OtcMaster::OPERATOR_USER)
    {
        try {
            return DB::transaction(function () use ($otc_master, $user, $operator_type) {
                $otc_master = $otc_master->lockForUpdate()->findOrFail($otc_master->getKey());
                throw_if(!in_array($otc_master->status, [self::STATUS_PAUSED, self::STATUS_OPENED]), new \Exception("当前状态不能下架"));
                $otc_master->status = self::STATUS_STOPPED;
                $otc_master->stoped_at = Carbon::now();
                $otc_master->save();
                // 生成日志
                $operator_map = OtcMaster::getOperatorMap();
                $operator_name = $operator_map[$operator_type];
                OtcMasterLog::create([
                    'master_id' => $otc_master->id,
                    'user_id' => $user->id ?? 0,
                    'operator_type' => $operator_type,
                    'status' => self::STATUS_STOPPED,
                    'memo' => __("model.下架挂单", ['operator_type' => __("model.{$operator_name}")]),
                ]);
                return $otc_master;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 取消挂单
     *
     * @param \App\Models\Otc\OtcMaster $otc_master
     * @return boolean
     */
    public static function cancelMaster($otc_master, $user = null, $operator_type = OtcMaster::OPERATOR_USER)
    {
        try {
            return DB::transaction(function () use ($otc_master, $user, $operator_type) {
                /** @var self $otc_master */
                $otc_master = $otc_master->lockForUpdate()->findOrFail($otc_master->getKey());
                throw_if($otc_master->status == self::STATUS_OPENED, new \Exception(__('model.要取消挂单,必须先暂停挂单交易')));
                throw_if($otc_master->status != self::STATUS_PAUSED, new \Exception(__('model.当前状态不能取消')));
                throw_if(
                    $otc_master->hasDealDetail() || bc($otc_master->dealing_qty, '>', 0),
                    new \Exception(__('model.当前挂单有交易进行中,不能取消,请等待交易结束再操作'))
                );
                throw_if(bc($otc_master->remain_qty, '<=', 0), new \Exception(__('model.剩余数量不足,不能取消')));
                // 如果是卖出,退回剩余交易数量
                if ($otc_master->way == self::WAY_SELL) {
                    $sell_account = LegalAccount::getAccountForLock($otc_master->currency_id, $otc_master->seller->user_id);
                    throw_unless($sell_account, new \Exception(__('model.商家该币种法币账户未找到')));
                    $sell_account->changeLockBalance(
                        AccountLog::LEGAL_POST_CANCEL,
                        -$otc_master->remain_qty,
                        ['memo' => __('otc.卖方取消场外交易')]
                    );
                    $sell_account->changeBalance(
                        AccountLog::LEGAL_POST_CANCEL,
                        $otc_master->remain_qty,
                        ['memo' => __('otc.卖方取消场外交易')]
                    );
                }
                $otc_master->remain_qty = 0; // 因为有已完成的数量,所以这里可以清零以避免重复取消
                $otc_master->status = self::STATUS_CANCELED;
                $otc_master->canceled_at = Carbon::now();
                $otc_master->save();
                // 生成日志
                $operator_map = OtcMaster::getOperatorMap();
                $operator_name = $operator_map[$operator_type];
                OtcMasterLog::create([
                    'master_id' => $otc_master->id,
                    'user_id' => $user->id ?? 0,
                    'operator_type' => $operator_type,
                    'status' => self::STATUS_CANCELED,
                    'memo' => __("model.取消挂单", ['operator_type' => __("model.{$operator_name}")]),
                ]);
                return $otc_master;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 完成交易
     *
     * @param self $otc_master
     * @param \App\Models\Otc\User|\App\Models\Admin\Admin|null $user
     * @param string|int $operator_type
     * @return self
     */
    public static function finishMaster($otc_master, $user = null, $operator_type = OtcMaster::OPERATOR_USER)
    {
        try {
            return DB::transaction(function () use ($otc_master, $user, $operator_type) {
                /** @var self $otc_master */
                $otc_master = $otc_master->lockForUpdate()->findOrFail($otc_master->getKey());
                throw_if(
                    in_array($otc_master->status, [self::STATUS_FINISHED, self::STATUS_CANCELED]),
                    new \Exception(__('model.交易状态已完成或已取消,不能再操作'))
                );
                // 交易中的数量检测
                throw_if(
                    $otc_master->hasDealDetail() || bc($otc_master->dealing_qty, '>', 0),
                    new \Exception(__('model.当前挂单有交易进行中,不能标记完成'))
                );
                // 剩余数量检测
                throw_if(bc($otc_master->remain_qty, '>', 0), new \Exception(__('model.当前剩余数量不为0,不能标记完成,建议先暂停等待交易完成后取消挂单')));
                $otc_master->status = self::STATUS_CANCELED;
                $otc_master->finished_at = Carbon::now();
                $otc_master->save();
                // 生成日志
                $operator_map = OtcMaster::getOperatorMap();
                $operator_name = $operator_map[$operator_type];
                OtcMasterLog::create([
                    'master_id' => $otc_master->id,
                    'user_id' => $user->id ?? 0,
                    'operator_type' => $operator_type,
                    'status' => self::STATUS_FINISHED,
                    'memo' => __("model.完成挂单", ['operator_type' => __("model.{$operator_name}")]),
                ]);
                return $otc_master;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
