<?php


namespace App\Models\Tx;

use App\Models\Account\ChangeAccount;
use App\Models\Currency\CurrencyMatch;
use App\Models\Model;
use App\Models\User\User;

abstract class Tx extends Model
{

    public function baseAccount()
    {
        return $this->belongsTo(ChangeAccount::class, 'base_account_id');
    }

    public function quoteAccount()
    {
        return $this->belongsTo(ChangeAccount::class, 'quote_account_id');
    }

    /**取消订单
     *
     * @return mixed
     */
    public abstract function cancel();

    /**退还余额
     * 只退还应退还的余额
     *
     * @return $this
     */
    public abstract function returnBalance($log_type);

    /**
     * 生成订单
     *
     * @param $user_id
     * @param $currencyMatch
     * @param $number
     * @param $price
     *
     * @return mixed
     */
    public static abstract function generateTx($user_id, $currencyMatch, $number, $price);


    /**推送到撮合引擎
     *
     * @return mixed
     */
    public abstract function toMatch();

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'uid' => __('model.未知')
        ]);
    }

    public function currencyMatch()
    {
        return $this->belongsTo(CurrencyMatch::class);
    }
}
