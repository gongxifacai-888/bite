<?php


namespace App\Models\Currency;

use App\Models\Model;

use App\Models\Setting\Setting;
use Illuminate\Support\Facades\Cache;

class CurrencyQuotation extends Model
{
    public $timestamps = false;

    protected $appends = [
//        'cny_price',
//        'usd_price',
    ];

    public function getCloseAttribute($value)
    {
        return bc($value, '+', 0, 4);
    }

    public function currencyMatch()
    {
        return $this->belongsTo(CurrencyMatch::class);
    }

    public function getCnyPriceAttribute()
    {
        return $this->currencyMatch->baseCurrency->cny_price ?? 0;
    }

    public function getUsdPriceAttribute()
    {
        return $this->currencyMatch->baseCurrency->usd_price ?? 0;
    }

    public function getAmountAttribute($amount)
    {
        return bc($amount, '+', 0, 4);
    }

    public function getVolumeAttribute($volume)
    {
        return bc($volume, '+', 0, 4);
    }

    /**更新行情
     *
     * @param float $close  收盘价
     * @param float $volume 成交量
     * @param float $amount 成交额
     *
     * @return CurrencyQuotation
     */
    public function updateQuotation($close, $volume, $amount)
    {
        $this->open == 0 && $this->open = $close;
        $this->low == 0 && $this->low = $close;
        $this->high == 0 && $this->high = $close;

        $this->low > $close && $this->low = $close;
        $this->high < $close && $this->high = $close;
        $this->close = $close;
        $this->volume += $volume;
        $this->amount += $amount;
        $change = ($this->close - $this->open) / $this->open * 100;
        $this->change = bc($change, '+', 0, 2);
        $this->save();

        return $this;
    }

    /**
     * 更新替换行情
     *
     * @param $close
     * @param $number
     * @param $amount
     *
     * @return CurrencyQuotation
     */
    public function replaceQuotation($data)
    {
        $this->close = $data['close'];
        $this->volume = $data['vol'];
        $this->amount = $data['amount'];
        $this->change = $data['change'];
        $this->open = $data['open'];
        $this->low = $data['low'];
        $this->high = $data['high'];
        $this->save();
        return $this;
    }
}
