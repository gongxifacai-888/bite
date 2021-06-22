<?php

namespace App\Models\AppSetting;

use App\Models\Model;

class AppVersion extends Model
{
    protected $appends = ['type_name'];

    const TYPE_ANDROID = 1;
    const TYPE_IOS = 2;

    public function getTypeNameAttribute()
    {
        if ($this->type == self::TYPE_ANDROID) {
            return 'Android';
        } else {
            return 'Ios';
        }
    }
}
