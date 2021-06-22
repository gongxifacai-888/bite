<?php

namespace App\Models\User;

use App\Models\Model;
use App\Models\System\Payway;

class UserPayway extends Model
{
    protected $casts = [
        'raw_data' => 'array',
    ];

    protected $appends = [
        'pay_code',
    ];

    public function payway()
    {
        return $this->belongsTo(Payway::class, 'payway_id', 'id');
    }

    public function getPayCodeAttribute()
    {
        return $this->payway->code ?? '';
    }
}
