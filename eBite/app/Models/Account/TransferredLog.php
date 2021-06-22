<?php


namespace App\Models\Account;

use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\User\User;

/**划转日志
 * Class TransferredLog
 *
 * @package App\Models\Account
 */
class TransferredLog extends Model
{

    protected $appends = [
        'from_name',
        'to_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getFromNameAttribute()
    {
        $from = $this->attributes['from'];
        return __("model.{$from}");
    }

    public function getToNameAttribute()
    {
        $to = $this->attributes['to'];
        return __("model.{$to}");
    }

}
