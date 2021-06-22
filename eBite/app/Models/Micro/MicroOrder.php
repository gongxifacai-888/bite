<?php

namespace App\Models\Micro;

use Illuminate\Support\Carbon;
use App\Models\Currency\{Currency, CurrencyMatch};
use App\Models\Model;
use App\Models\User\User;

class MicroOrder extends Model
{
    const TYPE_RISE = 1; //买涨
    const TYPE_FALL = 2; //买跌

    const STATUS_OPENED = 1; //交易中
    const STATUS_CLOSING = 2; //平仓中
    const STATUS_CLOSED = 3; //已平仓

    const RESULT_LOSS = -1; //亏
    const RESULT_BALANCE = 0; //平
    const RESULT_PROFIT = 1; //盈


    //结算状态
    const NOT_SETTLE = 0;
    const SETTLED = 1;

    protected $dates = [
        'handled_at',
        'complete_at',
    ];

    protected $attributes = [
        'profit_result' => 0,
    ];

    protected $hidden = [
        'pre_profit_result', //隐藏掉预设的盈利结果
    ];

    protected $appends = [
        'currency_code',
        'symbol_name',
        'account',
        'type_name',
        'status_name',
        'profit_result_name',
    ];

    protected static $typeList = [
        '',
        self::TYPE_RISE => '涨',
        self::TYPE_FALL => '跌',
    ];

    protected static $statusList = [
        '',
        self::STATUS_OPENED => '交易中',
        self::STATUS_CLOSING => '平仓中',
        self::STATUS_CLOSED => '已平仓',
    ];

    protected static $resultList = [
        self::RESULT_LOSS => '亏损',
        self::RESULT_BALANCE => '无',
        self::RESULT_PROFIT => '盈利',
    ];

    public function getCurrencyCodeAttribute()
    {
        return $this->currency->code ?? '';
    }

    public function getSymbolNameAttribute()
    {
        return $this->currencyMatch->symbol ?? '';
    }

    public function getTypeNameAttribute()
    {
        $value = $this->attributes['type'] ?? 0;
        return self::$typeList[$value] ?? '';
    }

    public function getStatusNameAttribute()
    {
        $value = $this->attributes['status'] ?? 0;
        return self::$statusList[$value] ?? '';
    }

    public function getProfitResultNameAttribute()
    {
        $value = $this->attributes['profit_result'] ?? 0;
        return self::$resultList[$value] ?? '';
    }

    public function getPreProfitResultNameAttribute()
    {
        $value = $this->attributes['pre_profit_result'] ?? 0;
        return self::$resultList[$value] ?? '';
    }

    public function getAccountAttribute()
    {
        return $this->user()->value('uid');
    }

    public function setPreProfitResultAttribute($value)
    {
        if (!$this->exists) {
            $user_id = $this->attributes['user_id']; //下单用户id
            $currency_id = $this->attributes['currency_id']; //币种id
            $number = $this->attributes['number']; //下单数量
            $result = self::getRisk($user_id, $currency_id, $number);
            $this->attributes['pre_profit_result'] = $result;
        } else {
            $this->attributes['pre_profit_result'] = $value;
        }
    }

    //
    public function getParentAgentNameAttribute()
    {
        $user = $this->user()->getResults();
        return $user ? $user->parent_agent_name : '';
    }

    public function getUserAgentLevelAttribute()
    {

        $user = $this->user()->getResults();
        return $user ? $user->my_agent_level : __('model.普通用户');
    }

    public function getSymbolAttribute()
    {
        return $this->currencyMatch->symbol ?? __('api.未知');
    }

    public function getMobileAttribute()
    {
        return $this->user->mobile ?? '';
    }

    public function getEmailAttribute()
    {
        return $this->user->email ?? '';
    }

    public function getUidAttribute()
    {
        return $this->user->uid ?? '';
    }

    /**
     * 返回风控结果
     *
     * @return void
     */
    public static function getRisk($user_id, $currency_id)
    {
        //检测用户是否在预设的盈利和亏损名单中
        //$risk = $this->user()->value('risk');
        //return $risk;
        return 0;
    }


    /**
     * 根据价格计算当前的盈利结果
     *
     * @return integer
     */
    public function getProfitTypeAttribute()
    {
        $type = $this->attributes['type'] ?? 0;
        $open_price = $this->attributes['open_price'] ?? 0;
        $end_price = $this->attributes['end_price'] ?? 0;
        if (empty($type) || empty($open_price) || empty($end_price)) {
            return 0;
        }
        $profit_type = bccomp($end_price, $open_price, 8);
        if ($type == self::TYPE_FALL) {
            $profit_type = -1 * $profit_type;
        }
        return $profit_type;
    }

    public function getCostPriceAttribute()
    {
        return $this->currency->price ?? 0;
    }

    public function getLossPriceAttribute()
    {
        return $this->currency->price ?? 0;
    }

    public function getCostAttribute()
    {
        $number = $this->attributes['number'] ?? 0;
        return bc($number, '*', $this->getAttribute('cost_price'));
    }

    public function getCostWithProfitAttribute()
    {
        $profit_ratio = $this->getAttribute('profit_ratio');
        $number = $this->attributes['number'] ?? 0;
        $cost_with_profit = bc(100, '+', $profit_ratio);
        $cost_with_profit = bc($cost_with_profit, '/', 100);
        $cost_with_profit = bc($cost_with_profit, '*', $number);
        return bc($cost_with_profit, '*', $this->getAttribute('cost_price'));
    }

    public function getRemainMilliSecondsAttribute()
    {
        $handled_at = $this->getAttribute('handled_at');
        $now = Carbon::now();
        if ($now >= $handled_at) {
            return 0;
        }
        return ($handled_at->diffInSeconds($now) + 0.3) * 1000;
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function currencyMatch()
    {
        return $this->belongsTo(CurrencyMatch::class, 'match_id');
    }
}
