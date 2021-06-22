<?php

namespace App\Utils\Market\Huobi\Http;

use Illuminate\Support\Facades\DB;
use App\Models\Currency\CurrencyMatch;
use App\Models\Tx\HuobiSymbol;
use App\Models\Currency\CurrencyQuotation;

class HuobiMarket
{
    protected static $config = [];

    protected static $huobiInterfaceInstance = null;

    protected $params = [];

    protected static $period = [
        '1min' => 5,
        '5min' => 6,
        '15min' => 1,
        '30min' => 7,
        '60min' => 2,
        '4hour' => 3,
        '1day' => 4,
        '1week' => 8,
        '1mon' => 9,
        '1year' => 10,
    ];

    public static function getConfig($key = null, $default = '')
    {
        if (is_null($key)) {
            return self::$config;
        } else {
            return self::$config[$key] ?? $default;
        }
    }

    public static function setConfig($key, $value = null)
    {
        if (is_array($key)) {
            self::$config = $key;
        } else {
            self::$config[$key] = $value;
        }
    }

    public function setParam($key, $value)
    {
        if (is_array($key)) {
            $this->params = $key;
        } else {
            $this->params[$key] = $value;
        }
    }

    /**
     * 获得一个火币接口实例
     *
     * @return \App\Utils\Market\Huobi
     */
    public static function getHuobiInterfaceInstance()
    {
        if (!self::$huobiInterfaceInstance) {
            $account_id = self::getConfig('account_id');
            $access_key = self::getConfig('access_key');
            $secret_key = self::getConfig('secret_key');
            self::$huobiInterfaceInstance = new Huobi($account_id, $access_key, $secret_key);
        }
        return self::$huobiInterfaceInstance;
    }


    /**
     * 获取K线
     *
     * @param string $symbol
     * @param string $period,可选值:1min, 5min, 15min, 30min, 60min, 1day, 1mon, 1week, 1year
     * @param integer $size
     * @return array
     */
    public static function getHistoryKline($symbol, $period, $size = 150)
    {
        $original_value = date_default_timezone_get();
        $huobi = self::getHuobiInterfaceInstance();
        $result = $huobi->get_history_kline($symbol, $period, $size);
        date_default_timezone_set($original_value);
        return $result;
    }

}
