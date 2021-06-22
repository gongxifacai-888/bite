<?php

namespace App\Jobs;

use App\Models\Account\Account;
use App\Models\Account\ChangeAccount;
use App\Models\Account\AccountLog;
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
class AsyncChangeBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $account_id;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $log_type;

    /**
     * @var float
     */
    protected $number;

    /**
     * @var bool
     */
    protected $is_lock;

    /**
     * @var string
     */
    protected $extra_data;

    /**
     * AsyncChangeBalance constructor.
     *
     * @param int   $account_id
     * @param array $log_type 日志类型
     * @param float $number   更改数量
     * @param int   $is_lock  是否更改的是锁定
     * @param array $extra_data
     */
    public function __construct($account_id, $log_type, $number, $is_lock = AccountLog::NO_LOCK, $extra_data = [])
    {
        $this->account_id = $account_id;
        $this->log_type = $log_type;
        $this->number = $number;
        $this->is_lock = $is_lock;
        $this->extra_data = $extra_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::transaction(function () {
                /**
                 * @var  $account Account
                 */
                $account = ChangeAccount::lockForUpdate()->find($this->account_id);
                if (!$account) {
                    return;
                }

                if ($this->is_lock==AccountLog::IS_LOCK) {
                    $account->changeLockBalance($this->log_type, $this->number, $this->extra_data);
                } else {
                    $account->changeBalance($this->log_type, $this->number, $this->extra_data);
                }
            });
        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getLine());
            dump($t->getFile());
        }
    }
}
