<?php


namespace App\Models\Account;

use App\Exceptions\ThrowException;

class LeverAccount extends Account
{
    /**
     * @var AccountLog
     */
    public $logClass = LeverAccountLog::class;

    /**改变币币账户余额
     *
     * @param       $log_type
     * @param       $number
     * @param array $extra_data
     *
     * @throws \Exception
     */
    public function changeBalance($log_type, $number, $extra_data = [])
    {
        //是否启用严格模式,如果不启用,则可以扣成负数
        $strict = $extra_data['strict'] ?? true;
        if ($number == 0) {
            return;
        }
        if ($number < 0 && $this->balance < abs($number) && $strict) {
            throw new ThrowException(__('model.余额不足'));
        }

        $before = $this->balance;
        $this->balance = bc($this->balance, '+', $number);
        $after = $this->balance;

        $this->save();

        $this->logClass::insertLog($this->user_id, $this->currency_id, $log_type, $before, $number, $after,
            $extra_data);
    }

    /**改变币币账户锁定余额
     *
     * @param       $log_type
     * @param       $number
     * @param array $extra_data
     *
     * @throws \Exception
     */
    public function changeLockBalance($log_type, $number, $extra_data = [])
    {
        //是否启用严格模式,如果不启用,则可以扣成负数
        $strict = $extra_data['strict'] ?? true;
        if ($number == 0) {
            return;
        }
        if ($number < 0 && $this->lock_balance < abs($number) && $strict) {
            throw new ThrowException(__('model.锁定余额不足'));
        }

        $before = $this->lock_balance;
        $this->lock_balance = bc($this->lock_balance, '+', $number);
        $after = $this->lock_balance;

        $this->save();

        $this->logClass::insertLog($this->user_id, $this->currency_id, $log_type, $before, $number, $after,
            $extra_data, AccountLog::IS_LOCK);
    }
}
