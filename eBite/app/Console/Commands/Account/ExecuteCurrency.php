<?php

namespace App\Console\Commands\Account;

use App\BlockChain\BlockChain;
use App\Models\Account\AccountType;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExecuteCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:executeCurrency {currency_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '上币脚本';

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
        try {

            $currency_id = $this->argument('currency_id');
            $currency = Currency::findOrFail($currency_id);
            $user_count = User::count();

            //循环创建去中心化钱包
            foreach ($currency->currencyProtocols()->cursor() as $currencyProtocol) {
                $blockChain = BlockChain::newInstance($currencyProtocol);

                DB::transaction(function () use ($blockChain, $user_count) {
                    foreach (User::cursor() as $k => $user) {
                        $blockChain->generateOnlineWallet($user);

                        $this->info("总数:{$user_count}个,已创建去中心化钱包{$k}个");
                    }
                });
            }

            foreach (User::cursor() as $k => $user) {
                DB::transaction(function () use ($user, $currency, $user_count, $k) {
                    //创建中心化账户
                    AccountType::generateUserAccount($user, $currency);

                    $this->info("总数:{$user_count}个,已创建中心化钱包{$k}个");
                });
            }
        } catch (\Throwable $t) {
            $this->error($t->getMessage());
            $this->error($t->getLine());
            $this->error($t->getFile());
        }
    }
}
