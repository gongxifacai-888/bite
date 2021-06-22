<?php


namespace App\Entity;

/**
 * Class TxOrder
 *
 * @package App\Entity
 *
 * @property int    $id                单子id
 * @property string $symbol            交易对
 * @property int    $currency_match_id 交易对id
 * @property float  $price             价格
 * @property int    $user_id           用户id
 * @property float  $amount            交易量
 * @property int    $market_from       行情来自哪里
 * @property float  $change_fee_rate   交易费率
 * @property int    $base_account_id   交易币钱包id
 * @property int    $quote_account_id  计价币钱包id
 */
class TxOrder extends BaseEntity
{

}
