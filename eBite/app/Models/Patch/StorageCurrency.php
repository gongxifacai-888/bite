<?php

namespace App\Models\Patch;


use App\Models\Currency\Currency;
use App\Models\Model;

class StorageCurrency extends Model
{
    const STATUS_ON  = 1;
    const STATUS_OFF = 2;

    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id')->select(['id', 'code']);
    }
}
