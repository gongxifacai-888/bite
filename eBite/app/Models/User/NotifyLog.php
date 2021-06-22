<?php


namespace App\Models\User;

use App\Models\Model;

/**通知日志
 * Class NotifyLog
 *
 * @package App\Models
 */
class NotifyLog extends Model
{
    protected $casts = [
        'content' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
