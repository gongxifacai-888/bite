<?php

namespace App\Models\Otc;

use App\Models\Model;
use App\Models\Currency\Currency;
use App\Models\Otc\Seller;
use App\Models\User\User;
class SellerCurrencyApply extends Model
{
    const STATUS_SUBMIT = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = 2;

    protected $appends = [
        'status_name',
        'currency_names',
        'seller_name',
        'uid',
    ];
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }
    public function getSellerNameAttribute()
    {
        return $this->seller->name ?? '';
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function getUidAttribute()
    {
        return $this->user->uid ?? '';
    }

}
