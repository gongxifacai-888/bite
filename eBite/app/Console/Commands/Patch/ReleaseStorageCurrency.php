<?php

namespace App\Console\Commands\Patch;

use App\Models\Account\Account;
use App\Models\Account\AccountLog;
use App\Models\Account\AccountType;
use App\Models\Account\FinancialAccount;
use App\Models\Patch\StorageCurrency;
use App\Models\Patch\StorageCurrencyHistory;
use App\Models\Setting\Setting;
use Illuminate\Console\Command;

class ReleaseStorageCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patch:releaseStorageCurrency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        foreach (StorageCurrencyHistory::with(['storageCurrency'])->where('status', StorageCurrencyHistory::STATUS_ON)
                     ->has('storageCurrency')->get() as $item) {
            $number = bc($item->storageCurrency->limit_number, '*', $item->storageCurrency->rate);
            $this->info("增加数量{$number}");
            //扣除天数
            $item->decrement('surplus_days');
            //增加累计收益
            $item->increment('pile_income', $number);
            //释放收益
            $account = Account::getAccountByType(AccountType::FINANCIAL_ACCOUNT_ID, $item->currency_id, $item->user_id);
            $account->changeBalance(AccountLog::FINANCIAL_INC, $number);
            if ($item->surplus_days <= 0) {
                $item->update(['status' => StorageCurrencyHistory::STATUS_OFF]);
                //本进退还给币币钱包
                $account = Account::getAccountByType(AccountType::CHANGE_ACCOUNT_ID, $item->currency_id, $item->user_id);
                $account->changeBalance(AccountLog::CURRENT_RELEASE, $item->storageCurrency->limit_number);
            }
        }
        //开始释放活期的
        foreach (FinancialAccount::where('balance', '>', 0)->get() as $item) {
            $release_number = bc($item->balance, '*', Setting::getValueByKey('current_rate', 0.01));
            $account = Account::getAccountByType(AccountType::FINANCIAL_ACCOUNT_ID, $item->currency_id, $item->user_id);
            $account->changeBalance(AccountLog::CURRENT_INC, $release_number);
        }
    }
}
