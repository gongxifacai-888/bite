<?php

namespace App\Models\Tx;

use App\Models\Model;
class HuobiSymbol extends Model
{
    public $timestamps = false;

    public static function getSymbolsData($symbols)
    {
        self::unguard();
        foreach ($symbols as $key => $value) {
            $huobi_symbol = new self();
            $huobi_symbol->fill($value)->save();
        }
        self::reguard();
        return true;
    }
}
