<?php

namespace App\Models\Chain;

use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\Model;
use App\Models\User\User;

class TxHash extends Model
{

    /**
     * 第一个为数据库里面的类型,
     * 第二个为默认备注,
     * 第三个为更新哈希时需要的回调方法
     * 详细见UpdateHashStatus
     *
     */
//0，充币，1归拢， 2提币，3 打入手续费
    const COLLECT  = [1, '归拢', 'collect'];
    const FEE      = [2, '打入手续费', 'fee'];
    const WITHDRAW = [3, '提币', 'draw'];
    const RECHARGE = [0, '链上充值到账', 'recharge'];

    const STATUS_WAIT    = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED  = 2;
    const STATUS_INVALID = 3;

    protected $appends = [
        'status_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function currencyProtocol()
    {
        return $this->belongsTo(CurrencyProtocol::class);
    }

    protected function getStatusNameAttribute()
    {
        $status = $this->attributes['status'];
        $name[self::STATUS_WAIT] = __('model.等待中');
        $name[self::STATUS_SUCCESS] = __('model.成功');
        $name[self::STATUS_FAILED] = __('model.失败');
        $name[self::STATUS_INVALID] = __('model.无效交易');
        return $name[$status] ?? __('model.未知');
    }

    /**
     * 写入日志
     *
     * @param     $user_id
     * @param     $currency_id
     * @param     $log_type
     * @param     $hash
     * @param int $currency_protocol_id 币种协议ID
     * @param int $status 默认0 成功1 失败2
     *
     * @return TxHash|\Illuminate\Database\Eloquent\Model
     */
    public static function insertHash(
        $user_id, $currency_id, $log_type, $hash, $currency_protocol_id, $status = self::STATUS_WAIT
    )
    {
        $type = $log_type[0];
        $memo = $log_type[1];
        $callback_handle = $log_type[2];
        return self::create([
            'user_id'              => $user_id,
            'currency_id'          => $currency_id,
            'type'                 => $type,
            'memo'                 => $memo,
            'callback_handle'      => $callback_handle,
            'hash'                 => $hash,
            'currency_protocol_id' => $currency_protocol_id,
            'status'               => $status,
        ]);
    }
    /**
     * 获取此类的账户类型
     * @return array
     * @throws \ReflectionException
     */
    public static function getHashTypes()
    {
        $reflect = new \ReflectionClass(self::class);
        $type_arr = $reflect->getConstants();
        $arr = [];
        foreach ($type_arr as $v) {
            if (is_array($v)) {
                $arr[] = $v;
            }
        }
        return $arr;
    }
}
