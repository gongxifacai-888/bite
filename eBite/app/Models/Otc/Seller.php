<?php

namespace App\Models\Otc;

use Illuminate\Support\Facades\DB;

use App\Models\Model;
use App\Models\User\User;

class Seller extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 商家币种关联(一对多)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sellerCurrencies()
    {
        return $this->hasMany(SellerCurrency::class, 'seller_id', 'id');
    }

    public function otcMasters()
    {
        return $this->hasMany(OtcMaster::class, 'seller_id', 'id');
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

    /**
     * 检测用户是否是商家
     *
     * @param \App\Models\User\User $user 检检测的用户模型
     * @param boolean $valid 状态是否是有效的商家
     * @return boolean
     */
    public static function isSeller($user, $valid = true)
    {
        return self::where('user_id', $user->id)->when($valid, function ($query) {
            $query->where('status', 1);
        })->exists();
    }

    /**
     * 添加用户为商家
     *
     * @param \App\Models\User\User $user
     * @param string $name 商家名称
     * @param array $currencies 币种权限
     * @param integer $status 状态
     * @return \App\Models\Otc\Seller
     * @throws \Exception
    */
    public static function addSeller($user, $name, $currencies, $status = 0)
    {
        try {
            return DB::transaction(function () use ($user, $name, $currencies, $status) {
                // 判断用户是否已是商家
                throw_if($user->isSeller(false), new \Exception(__('model.操作失败:用户已是商家!')));
                $seller_data = [
                    'user_id' => $user->id,
                    'name' => $name,
                    'status' => $status,
                ];
                $seller = self::create($seller_data);
                // 排除掉商家已经存在的币种
                $has_currencies = SellerCurrency::where('seller_id', $user->seller->id)
                    ->pluck('currency_id')
                    ->all();
                $currencies = array_diff($currencies, $has_currencies);
                throw_if(empty($currencies), new \Exception(__('model.币种不能为空或选择币种已经存在')));
                $seller_currency_data = array_map(function ($item) use ($status) {
                    return new SellerCurrency([
                        'currency_id' => $item,
                        'buy_status' => $status,
                        'sell_status' => $status,
                    ]);
                }, $currencies);
                // 插入商家多币种权限
                $seller->sellerCurrencies()->saveMany($seller_currency_data);
                return $seller;
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
