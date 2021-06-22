<?php

namespace App\Console\Commands\Market;

use App\Logic\Market;
use App\Models\Currency\CurrencyMatch;
use Illuminate\Console\Command;

class ImportKline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:importKline {currency_match_id} {other_symbol?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入k线到redis';

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
        $this->info('开始导入k线数据');

        $currency_match_id = $this->argument('currency_match_id');
        $other_symbol = $this->argument('other_symbol');

        $currencyMatch = CurrencyMatch::findOrFail($currency_match_id);

        $this->info('正在导入:' . $currencyMatch->symbol);
        Market::importKline($currencyMatch, $other_symbol);

        $this->info('导入完成');
    }
}
