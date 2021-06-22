<?php


namespace App\Models\Currency;

use App\Logic\Market;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Model;
use App\Models\Tx\{HuobiSymbol, TxIn, TxOut};

class CurrencyMatch extends Model
{
    //行情来自
    const EXCHANGE = 0;
    const ROBOT = 1;
    const HUOBI = 2;

    //打开关闭
    const ON = 1;
    const OFF = 0;

    protected $appends = [
        'symbol',
        'base_currency_code',
        'quote_currency_code',
        'lower_symbol'
    ];


    public function getMarketFromNameAttribute()
    {
        $value = $this->getAttribute('market_from');
        $name[self::EXCHANGE] = __('model.交易所');
        $name[self::ROBOT] = __('model.机器人');
        $name[self::HUOBI] = __('model.火币');
        return $name[$value] ?? __('model.未知');
    }

    public function getBaseCurrencyCodeAttribute()
    {
        return $this->baseCurrency->code ?? __('model.未知');
    }

    public function getQuoteCurrencyCodeAttribute()
    {
        return $this->quoteCurrency->code ?? __('model.未知');
    }

    public function getSymbolAttribute()
    {
        $base_currency_code = $this->getAttribute('base_currency_code');
        $quote_currency_code = $this->getAttribute('quote_currency_code');
        return "{$base_currency_code}/{$quote_currency_code}";
    }

    public function getLowerSymbolAttribute()
    {
        $base_currency_code = $this->getAttribute('base_currency_code');
        $quote_currency_code = $this->getAttribute('quote_currency_code');
        return strtolower($base_currency_code . $quote_currency_code);
    }

    public static function getHuobiMatchs()
    {
        $currency_match = self::where('market_from', self::HUOBI)->get();
        $huobi_symbols = HuobiSymbol::pluck('symbol')->all();
        //过滤掉不在火币中的交易对
        $currency_match = $currency_match->filter(function ($value, $key) use ($huobi_symbols) {
            return in_array($value->lower_symbol, $huobi_symbols);
        });
        return $currency_match;
    }

    /**
     * 获取一个交易对(优先从缓存中获取减少数据库开销)
     *
     * @param string $base_currency 基础币名称
     * @param string $quote_currency 计价币名称
     * @param bool $refresh 是否制刷新
     * @return \App\Models\Currency\CurrencyMatch|null
     */
    public static function getSymbolMatch($base_currency, $quote_currency, $refresh = false)
    {
        $symbol = "{$base_currency}/{$quote_currency}";
        $cache_key = "cache_match_symbol:{$symbol}";
        if (!$refresh && Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }
        $match = self::whereHas('baseCurrency', function ($query) use ($base_currency) {
            $query->where('code', $base_currency);
        })->whereHas('quoteCurrency', function ($query) use ($quote_currency) {
            $query->where('code', $quote_currency);
        })->first();
        $match && Cache::put($cache_key, $match, Carbon::now()->addMinutes(1)); // 10分钟缓存
        return $match;
    }

    /**
     * 获取一个交易对(优先从缓存中获取减少数据库开销)
     *
     * @param int $id 交易对id
     * @param bool $refresh 是否制刷新
     * @return \App\Models\Currency\CurrencyMatch|null
     */
    public static function findMatch($id, $refresh = false)
    {
        $key = "cache_match_id:{$id}";
        $cache_has = Cache::has($key);
        if ($refresh || !$cache_has) {
            $match = self::find($id);
            Cache::put($key, $match, Carbon::now()->addMinutes(1));
            return $match;
        }
        return Cache::get($key, null);
    }

    public function quoteCurrency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function baseCurrency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function currencyQuotation()
    {
        return $this->hasOne(CurrencyQuotation::class);
    }

    public function txIn()
    {
        return $this->hasMany(TxIn::class);
    }

    public function txOut()
    {
        return $this->hasMany(TxOut::class);
    }

    /**
     * 取交易对最新价格(优先从行情获取)
     *
     * @return float|integer|string
     * @throws \Exception
     */
    public function getLastPrice()
    {
        // 优先从Cache取最新价格
        $last_price = 0;
        $last = Market::getKline("{$this->symbol}", '1min', 1);
        if (count($last) > 0) {
            $last = $last->first();
            $last_price = $last->close;
        } else {
            // 再从行情取最新价格
            $last = CurrencyQuotation::where('currency_match_id', $this->attributes['id'])->first();
            $last && $last_price = $last->close;
        }
        throw_if(bc($last_price, '<=', 0), new \Exception(__("model.获取行情价格异常")));
        return $last_price;
    }
}
