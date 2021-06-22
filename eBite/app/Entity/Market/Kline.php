<?php

namespace App\Entity\Market;

use App\Entity\BaseEntity;
use ArrayAccess;
use JsonSerializable;

/**
 * Class Kline
 *
 * @package App\Entity
 *
 * @property int          $id                         id
 * @property string       $period                     周期
 * @property string       $base-currency              基础币种
 * @property string       $quote-currency             报价币种
 * @property float|string $open                       开盘价
 * @property float|string $close                      收盘价
 * @property float|string $high                       最高价
 * @property float|string $low                        最低价
 * @property float|string $amount                     以基础币种计量的交易量
 * @property float|string $volume                     以报价币种计量的交易量
 * @property int          $market_from                行情来自哪里
 * @property int          $time                       时间
 * @property int          $currency_match_id          交易对
 */
class Kline extends BaseEntity implements JsonSerializable
{
    public function __construct()
    {
    }

    public function jsonSerialize()
    {
        isset($this->time) || $this->time = $this->id * 1000;
        return $this;
    }
}
