<?php

namespace App\Console\Commands\Market;

use App\Models\Currency\Currency;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

/**更新系统中的币种价值
 * Class UpdateCurrencyPrice
 *
 * @package App\Console\Commands\Market
 */
class UpdateCurrencyPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:updateCurrencyPrice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新系统中的币种价值';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->updateUSDPrice();
        $this->updateCNYPrice();
    }

    public function updateCnyPrice()
    {
        $quote = strtoupper('CNY');

        $res = http('https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest', [
            'start' => '1',
            'limit' => '10',
            'convert' => $quote
        ], 'GET', [
            'Accepts' => 'application/json',
            'X-CMC_PRO_API_KEY' => 'b24f7fca-e615-471f-8b69-aa334253a2eb'
        ]);

        $data = collect($res['data']);

        $currencies = Currency::get();
        $symbols = $currencies->pluck('code')->toArray();

        //过滤之后系统内需要的数据
        $new_arr = $data->filter(function ($item) use ($symbols, $quote) {
            return in_array($item['symbol'], $symbols);
        })->map(function ($item) use ($quote) {
            return [
                'code' => $item['symbol'],
                'price' => round($item['quote'][$quote]['price'], 8),
            ];
        })->values();
       // dd($new_arr);
        foreach ($new_arr as $item) {
            Currency::where('code', $item['code'])->update([
                'cny_price' => $item['price']
            ]);
        }
    }

    public function updateUSDPrice()
    {
        $quote = strtoupper('USD');

        $res = http('https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest', [
            'start' => '1',
            'limit' => '100',
            'convert' => $quote
        ], 'GET', [
            'Accepts' => 'application/json',
            'X-CMC_PRO_API_KEY' => 'b24f7fca-e615-471f-8b69-aa334253a2eb'
        ]);

        $data = collect($res['data']);
    //    dd($data);
        $currencies = Currency::get();
        $symbols = $currencies->pluck('code')->toArray();

        //过滤之后系统内需要的数据
        $new_arr = $data->filter(function ($item) use ($symbols, $quote) {
            return in_array($item['symbol'], $symbols);
        })->map(function ($item) use ($quote) {
            return [
                'code' => $item['symbol'],
                'price' => round($item['quote'][$quote]['price'], 8),
            ];
        })->values();
    //    dd($new_arr);
        foreach ($new_arr as $item) {
           $info= Currency::where('code', $item['code'])->update([
                'usd_price' => $item['price']
	]);
         // dd($info);
        }
    }
}
