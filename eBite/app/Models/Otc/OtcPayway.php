<?php

namespace App\Models\Otc;

use App\Models\Model;
use App\Models\System\Payway;

class OtcPayway extends Model
{
    protected $casts = [
        'raw_data' => 'array',
    ];

    protected $appends = [
        'pay_code',
    ];

    public function otcDetail()
    {
        return $this->belongsTo(OtcDetail::class, 'detail_id', 'id');
    }

    public function payway()
    {
        return $this->belongsTo(Payway::class, 'payway_id', 'id');
    }

    public function getPayCodeAttribute()
    {
        return $this->payway->code ?? '';
    }
}
