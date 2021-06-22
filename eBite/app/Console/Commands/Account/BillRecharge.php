<?php

namespace App\Console\Commands\Account;

use App\BlockChain\BlockChain;
use App\Models\Currency\Currency;
use App\Models\Account\Account;
use App\Models\Currency\CurrencyProtocol;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BillRecharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:billRecharge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步链上余额,同时充值';

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
     * @throws \Exception
     */
    public function handle()
    {
        try {

            foreach (CurrencyProtocol::with('currency')->cursor() as $currencyProtocol) {
                $blockChain = BlockChain::newInstance($currencyProtocol);

                if ($blockChain->recharge_method == BlockChain::BILL_METHOD) {
                    $this->info("开始更新{$currencyProtocol->currency->code}");
                    $this->billRecharge($blockChain);
                }
            }
        } catch (\Throwable $t) {
            $this->error($t->getMessage());
            $this->error($t->getLine());
            $this->error($t->getFile());
        }
    }

    /**
     * @param BlockChain $blockChain
     */
    public function billRecharge($blockChain)
    {
        try {
            DB::transaction(function () use ($blockChain) {
                $blockChain->billRecharge($blockChain);
            });
        } catch (\Throwable $t) {
            $this->error($t->getMessage());
            $this->error($t->getFile());
            $this->error($t->getLine());
        }
    }
}
