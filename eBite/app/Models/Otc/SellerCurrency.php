<?php

namespace App\Models\Otc;

use App\Models\Account\AccountType;
use App\Models\Currency\Currency;
use App\Models\Model;

class SellerCurrency extends Model
{
    protected $appends = [
        'currency_code',
        'seller_name',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function getCurrencyCodeAttribute()
    {
        return $this->currency->code ?? '';
    }

    public function getSellerNameAttribute()
    {
        return $this->seller->name ?? '';
    }

    /**
     * 查询系统中的法币
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLegalCurrencies()
    {
        $currencies = Currency::all();
        $legal_account = AccountType::where('account_code', 'legal_account')
            ->firstOrFail();
        $currencies = $currencies->filter(function ($item) use ($legal_account) {
            return in_array($legal_account->id, $item->accounts_display);
        })->values();
        return $currencies;
    }
}
