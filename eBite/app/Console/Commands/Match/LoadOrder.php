<?php

namespace App\Console\Commands\Match;

use App\Jobs\MatchEngine;
use App\Models\Currency\CurrencyMatch;
use App\Models\Setting\Setting;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use Illuminate\Console\Command;

class LoadOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:loadOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '载入单子';

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
        $this->comment('开始载入撮合订单到内存');

        //先关闭撮合总开关
        Setting::where('key', 'match_on')->first()->update([
            'value' => 0
        ]);

        $this->call('match:clearOrder');

        $currency_matches = CurrencyMatch::where('open_change', CurrencyMatch::ON)->get();

        foreach ($currency_matches as $currencyMatch) {
            $this->comment("正在载入{$currencyMatch->symbol}单子");

            $this->loadOrder($currencyMatch);
        }

        //开启撮合总开关
        Setting::where('key', 'match_on')->first()->update([
            'value' => 1
        ]);

        $this->comment('载入撮合订单到内存完成');
        $this->info('----------------------------------');
    }

    public function loadOrder($currencyMatch)
    {
        /** @var $txIn TxIn* */
        foreach (TxIn::where('currency_match_id', $currencyMatch->id)
                     ->orderBy('id', 'DESC')->cursor() as $txIn) {
            $this->comment("载入买入单:{$txIn->id}");
            $txIn->toMatch();
        }

        /** @var $txOut TxOut* */
        foreach (TxOut::where('currency_match_id', $currencyMatch->id)
                     ->orderBy('id', 'DESC')->cursor() as $txOut) {
            $this->comment("载入卖出单:{$txOut->id}");
            $txOut->toMatch();
        }
    }
}
