<?php

namespace App\Models\Micro;


use App\Models\Model;

class MicroSecond extends Model
{
    const STATUS_ON = 1;
    const STATUS_OFF = 0;

    public function setProfitRatioAttribute($profit_ratio)
    {
        if ($profit_ratio > 1) {
            $profit_ratio = 1;
        }
        $this->attributes['profit_ratio'] = $profit_ratio;
    }
}
