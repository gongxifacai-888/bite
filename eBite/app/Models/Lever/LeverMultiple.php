<?php

namespace App\Models\Lever;

use App\Models\Model;

class LeverMultiple extends Model
{
    protected $table = 'lever_multiple';
    public $timestamps = false;

    public function getQuotesAttribute()
    {
        return unserialize($this->attributes['quotes']);
    }
}
