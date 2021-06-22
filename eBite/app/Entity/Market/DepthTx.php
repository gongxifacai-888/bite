<?php


namespace App\Entity\Market;


use App\Entity\BaseEntity;

/**
 * 深度中的单子,买卖格式一样
 *
 * Class TxBuy
 *
 * @property
 *
 * @package App\Entity\Market
 */
class DepthTx extends BaseEntity
{
    /**
     * @var 买入量
     */
    public $amount;

    /**
     * @var 买入价
     */
    public $price;

    /**
     * @var float 在深度中的累计,由Depth实体类自动设置
     */
    public $sum;

    /**
     * @var string 在深度中的索引,由Depth实体类自动设置[买1,卖2]
     */
    public $index;

    /**
     * TxBuy constructor.
     *
     * @param float $amount 买入量
     * @param float $price  买入价
     */
    public function __construct($amount, $price)
    {
        $this->amount = $amount;
        $this->price = $price;
    }
}
