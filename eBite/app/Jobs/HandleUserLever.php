<?php

namespace App\Jobs;

use App\Models\Account\Account;
use App\Models\Account\ChangeAccount;
use App\Models\Account\AccountLog;
use App\Models\Currency\CurrencyMatch;
use App\Models\Lever\LeverTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

/**异步更改用户币币账户余额
 * 注意!
 * 正式余额不能异步扣除,只能增加
 * 一般用于撮合
 *
 * Class AsyncChangeBalance
 *
 * @package App\Jobs
 */
class HandleUserLever implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var CurrencyMatch
     */
    public $currency_match;

    /**
     *
     *
     */
    public function __construct($user_id, $currency_match)
    {
        $this->user_id = $user_id;
        $this->currency_match = $currency_match;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            LeverTransaction::handleUserLever($this->user_id, $this->currency_match);
        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getLine());
            dump($t->getFile());
        }
    }
}
