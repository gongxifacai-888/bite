<?php

namespace App\Console\Commands\Match;

use App\Jobs\MatchEngine;
use App\Models\Currency\CurrencyMatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:clearOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将撮合订单从redis中清空';

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
        $this->info('----------------------------------');
        $this->comment('开始清空内存中的撮合订单');

        $currency_matches = CurrencyMatch::where('open_change', CurrencyMatch::ON)->get();

        foreach ($currency_matches as $currencyMatch) {
            $this->comment("正在清空{$currencyMatch->symbol}单子");

            $this->clearOrder($currencyMatch);
        }

        $this->comment('清空内存中的撮合订单完成');
        $this->info('----------------------------------');
    }

    public function clearOrder($currencyMatch)
    {
        //清空
        MatchEngine::setOrders($currencyMatch->symbol, MatchEngine::IN, []);
        MatchEngine::setOrders($currencyMatch->symbol, MatchEngine::OUT, []);
    }
}
