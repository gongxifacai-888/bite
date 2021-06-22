<?php

namespace App\Console\Commands\Market;

use App\Logic\Market;
use App\Models\Currency\CurrencyMatch;
use Illuminate\Console\Command;

class ClearKline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:clearKline {currency_match_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除某个交易对的k线';

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
        $this->info('开始清除k线数据');

        $currency_match_id = $this->argument('currency_match_id');

        $currency_matches = CurrencyMatch::when($currency_match_id, function ($query, $currency_match_id) {
            $query->where('id', $currency_match_id);
        })->get();

        foreach ($currency_matches as $currencyMatch) {
            $this->info('正在清除:'.$currencyMatch->symbol);
            Market::clearKline($currencyMatch);
        }

        $this->info('清除完成');
    }
}
