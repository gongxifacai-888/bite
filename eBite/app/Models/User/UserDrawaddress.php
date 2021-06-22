<?php


namespace App\Models\User;

use App\Models\Model;

class UserDrawaddress extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
