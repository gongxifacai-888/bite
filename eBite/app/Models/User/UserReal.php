<?php


namespace App\Models\User;

use App\Models\Model;

class UserReal extends Model
{
    const REJECT = 1;
    const CONFORM = 2;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
