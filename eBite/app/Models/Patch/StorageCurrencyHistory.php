<?php

namespace App\Models\Patch;

use App\Models\Model;
use App\Models\User\User;

class StorageCurrencyHistory extends Model
{
    const STATUS_ON    = 1;
    const STATUS_OFF   = 2;

    public function storageCurrency()
    {
        return $this->hasOne(StorageCurrency::class, 'id', 'storage_currency_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
