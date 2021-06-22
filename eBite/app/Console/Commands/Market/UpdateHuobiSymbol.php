<?php

/**
 * swl
 *
 * 20180705
 */

namespace App\Console\Commands\Market;

use App\Models\Tx\HuobiSymbol;
use App\Utils\RPC;
use App\Models\User\User;
use App\Models\Account\Account;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class UpdateHuobiSymbol extends Command
{
    protected $signature = 'market:updateHuobiSymbol';

    protected $description = '更新火币交易对';

    public function handle()
    {

        $this->comment("start");
        $url = 'api.huobi.br.com/v1/common/symbols';

        $cli = new Client();
        $content = $cli->get($url)->getBody()->getContents();
        $content = json_decode($content, true);

        HuobiSymbol::truncate();
        try {
            foreach ($content['data'] as $v) {
                HuobiSymbol::create(
                    [
                        'base-currency' => $v['base-currency'],
                        'quote-currency' => $v['quote-currency'],
                        'amount-precision' => $v['amount-precision'],
                        'symbol-partition' => $v['symbol-partition'],
                        'symbol' => $v['symbol'],
                        'price-precision' => $v['price-precision']
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->comment($e->getMessage());
        }

        $this->comment("end");
    }


}
