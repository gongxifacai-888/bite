<?php


namespace App\Models\Tx;

use App\Models\Currency\CurrencyMatch;
use App\Models\Model;
use App\Models\User\User;

class Robot extends Model
{
    const STATUS_OFF = 0;
    const STATUS_ON = 1;

    protected $appends = [
        'status_name',
        'currency_match_symbol',
    ];

    public function getCurrencyMatchSymbolAttribute()
    {
        return $this->currencyMatch->symbol ?? __('model.未知');
    }

    public function getStatusNameAttribute()
    {
        $status = $this->getAttribute('status');
        $name[self::STATUS_OFF] = __('model.已关闭');
        $name[self::STATUS_ON] = __('model.已开启');
        return $name[$status] ?? __('model.未知');
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
