<?php

namespace App\Console\Commands\Market;

use App\Models\Currency\CurrencyQuotation;
use Illuminate\Console\Command;

class ClearQuotation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:clearQuotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清空今日成交量和成交额';

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
        CurrencyQuotation::whereRaw(true)->update([
            'volume' => 0,
            'amount' => 0,
            'open' => 0,
            'close' => 0,
            'high' => 0,
            'low' => 0,
            'change' => 0,
        ]);
    }
}
