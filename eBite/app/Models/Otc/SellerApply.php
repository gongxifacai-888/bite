<?php

namespace App\Models\Otc;

use App\Models\Model;
use App\Models\User\User;
use App\Models\Currency\Currency;

class SellerApply extends Model
{
    //
    const STATUS_SUBMIT = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = 2;

    protected $appends = [
        'status_name',
        'currency_names',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getMobileAttribute()
    {
        return $this->user->mobile ?? '';
    }

    public function getEmailAttribute()
    {
        return $this->user->email ?? '';
    }
    public function getUidAttribute()
    {
        return $this->user->uid ?? '';
    }

    public function getStatusNameAttribute()
    {
        $status = $this->attributes['status'];
        $name = [
            self::STATUS_SUBMIT => __('model.申请中'),
            self::STATUS_PASS => __('model.通过'),
            self::STATUS_REFUSE => __('model.拒绝'),

        ];
        return array_key_exists($status,  $name) ? $name[$status] : __('model.未知');
    }

    public function getCurrencyNamesAttribute(){
        $currencies = explode(",", $this->attributes['currencies']);
        $collection = Currency::whereIn("id", $currencies)->get();
        $name = "";
        foreach ($collection as $c) {
            $name .= "$c->code ,";
        }
        return trim($name, ",");

    }


}
