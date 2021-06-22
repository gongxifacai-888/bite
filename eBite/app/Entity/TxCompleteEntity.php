<?php


namespace App\Entity;

/**撮合里面用的交易完成
 * Class TxCompleteEntity
 *
 * @package App\Entity
 *
 * @property float amount             交易量
 * @property float price              价格
 * @property int   $way               买卖方向
 * @property int   $in_user_id        买入用户id
 * @property int   $out_user_id       卖出用户id
 * @property int   $currency_match_id 交易对id
 * @property int   $timestamp         时间戳
 * @property float $volume            交易额
 */
class TxCompleteEntity extends BaseEntity
{

    public function setPriceAttribute($price)
    {
        $this->price = bc($price, '+', 0, 4);
    }

    public function setAmountAttribute($amount)
    {
        $this->amount = bc($amount, '+', 0, 4);
    }

    /**交易额
     *
     * @param null|int $decimal
     *
     * @return bool|int|string|null
     */
    public function getVolumeAttribute($decimal = 8)
    {
        return bc($this->amount, '*', $this->price, $decimal);
    }

}
