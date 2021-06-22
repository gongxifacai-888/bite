<?php

namespace App\Models\Micro;


use App\Models\Currency\Currency;
use App\Models\Model;

class MicroNumber extends Model
{
    protected $appends = ['currency_code'];

    public function getCurrencyCodeAttribute()
    {
        return $this->currency->code ?? '币种不存在';
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
