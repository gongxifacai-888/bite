<?php


namespace App\Models\Tx;

use App\Models\Model;
use App\Models\User\User;

class TxComplete extends Model
{
    const IN = 2;
    const OUT = 1;

    protected $appends = [
        'way_name',
    ];

    public function getWayNameAttribute()
    {
        $value = $this->getAttribute('way');
        $name[2] = __('model.买入成交');
        $name[1] = __('model.卖出成交');
        return $name[$value] ?? __('model.未知');
    }

    public function inUser()
    {
        return $this->belongsTo(User::class, 'in_user_id');
    }

    public function outUser()
    {
        return $this->belongsTo(User::class, 'out_user_id');
    }
}
