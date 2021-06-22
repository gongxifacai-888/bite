<?php

/**
 * Created by PhpStorm.
 * User: LDH
 */

namespace App\Models\User;

use App\Models\Model;

class Token extends Model
{

    /**获取token值
     *
     * @return string
     */
    public static function getToken()
    {
        return request()->header('AUTHORIZATION', '');
    }

    /**设置token
     *
     * @param $user_id
     * @param $timeout_days
     *
     * @return string
     */
    public static function setToken($user_id, $timeout_days = 1)
    {
        $token = md5($user_id . time() . rand(0, 99999));

        self::create([
            'user_id' => $user_id,
            'timeout_at' => now()->addDays($timeout_days),
            'token' => $token,
        ]);
        return $token;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**根据token获取user_id
     *
     * @param $token
     *
     * @return int
     */
    public static function getUserIdByToken($token)
    {
        if (empty($token)) {
            return 0;
        }
        $token = self::where('token', $token)->first();
        if (empty($token)) {
            return 0;
        }
        return $token->user_id;
    }
}
