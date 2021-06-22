<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-06-04 16:32:08
 */

namespace App\Models\Otc;

use App\Models\Model;
use App\Models\User\User;

class OtcAction extends Model
{
    protected $casts = [
        'public_msg' => 'array',
        'to_buy_msg' => 'array',
        'to_sell_msg' => 'array',
    ];

    public function detail()
    {
        return $this->belongsTo(OtcDetail::class, 'detail_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function buyUser()
    {
        return $this->belongsTo(User::class, 'buy_user_id', 'id');
    }

    public function sellUser()
    {
        return $this->belongsTo(User::class, 'sell_user_id', 'id');
    }
    public function getUserAccountAttribute()
    {
        return $this->user->uid ?? __('model.未知');
    }
    public function getBuyUserAccountAttribute()
    {
        return $this->buyUser->uid ?? __('model.未知');
    }

    public function getMemoAttribute($value)
    {
        $memo = __("otc.{$value}");
        $memo = str_replace('otc.', '', $memo);
        return $memo;
    }

    public function getSellUserAccountAttribute()
    {
        return $this->sellUser->uid ?? __('model.未知');
    }
}
