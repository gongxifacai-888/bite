<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-27 09:12:33
 */

namespace App\Models\System;

use App\Models\Model;

class Payway extends Model
{
    //
    public function getNameAttribute()
    {
        $name = $this->attributes['name'];
        return __('model.' . $name);
    }
}
