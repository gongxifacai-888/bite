<?php


namespace App\Models\Tx;

use App\Models\Currency\CurrencyMatch;
use App\Models\Model;

use App\Jobs\MatchEngine;
use App\Models\User\User;

class TxHistory extends Model
{
    const IN = MatchEngine::IN;
    const OUT = MatchEngine::OUT;

    protected $appends = [
        'way_name',
        'account',
        'avg_price',
        'status_name',
    ];

    public function getStatusNameAttribute()
    {
        $number = $this->getAttribute('number');
        $transacted_amount = $this->getAttribute('transacted_amount');
        if ($transacted_amount == 0) {
            return __('model.已撤销');
        }
        if ($transacted_amount < $number) {
            return __('model.部分成交');
        }
        if ($transacted_amount >= $number) {
            return __('model.全部成交');
        }
    }

    public function getAvgPriceAttribute()
    {
        $volume = $this->getAttribute('transacted_volume');
        $amount = $this->getAttribute('transacted_amount');
        if ($amount <= 0) {
            return 0;
        }
        $avg_price = bc($volume, '/', $amount);
        return parse_price($avg_price);
    }

    public function getWayNameAttribute()
    {
        $value = $this->getAttribute('way');
        $name[self::IN] = __('model.买入');
        $name[self::OUT] = __('model.卖出');
        return $name[$value] ?? __('model.未知');
    }

    public function getAccountAttribute()
    {
        return $this->user->uid ?? __('model.未知');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currencyMatch()
    {
        return $this->belongsTo(CurrencyMatch::class);
    }
}
