<?php

namespace App\Models\Account;


use App\Models\Currency\Currency;
use App\Models\User\User;

class FinancialAccountLog extends AccountLog
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
