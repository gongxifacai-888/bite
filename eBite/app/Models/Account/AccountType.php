<?php


namespace App\Models\Account;

use ThrowException;
use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\User\User;

/**账户类型表
 * Class AccountType
 *
 * @package App\Models
 */
class AccountType extends Model
{
    //id和id和类的映射必须同步更新
    const CHANGE_ACCOUNT_ID    = 1;
    const LEGAL_ACCOUNT_ID     = 2;
    const LEVER_ACCOUNT_ID     = 3;
    const MICRO_ACCOUNT_ID     = 4;
    const FINANCIAL_ACCOUNT_ID = 5;

    const ACCOUNT_CLASSES = [
        self::CHANGE_ACCOUNT_ID    => ChangeAccount::class,
        self::LEGAL_ACCOUNT_ID     => LegalAccount::class,
        self::LEVER_ACCOUNT_ID     => LeverAccount::class,
        self::MICRO_ACCOUNT_ID     => MicroAccount::class,
        self::FINANCIAL_ACCOUNT_ID => FinancialAccount::class,
    ];
    const IS_RECHARGE     = 1;
    const NO_RECHARGE     = 2;

    const STATUS_ON  = 1;
    const STATUS_OFF = 2;
    public $timestamps = false;

    /**获取某个用户的所有账户
     *
     * @param int $user_id 用户id
     * @param bool $filter_display 是否展示币种内不显示的账户
     *
     * @return mixed
     */
    public function getUserAccounts($user_id, $filter_display = true)
    {
        $class = self::getAccountClass($this->id);

        $accounts = $class::whereHas('currency')
            ->where('user_id', $user_id)
            ->get(['id', 'user_id', 'currency_id', 'balance', 'lock_balance']);

        //过滤掉已隐藏的账户
        $accounts = $accounts->filter(function ($account) use ($filter_display) {
            /**@var Account $account * */
            $account->addHidden('user_id');
            $account->currency->append('account_types', 'is_recharge_account')->addHidden('desc');
            $account->append('convert_usd', 'convert_cny', 'currency_code', 'sum_balance');
            return in_array($this->id, $account->currency->accounts_display) && $filter_display;
        })->values();

        return $accounts;
    }


    public function getNameAttribute($value)
    {
        $name = $this->attributes['name'];
        $name = __("model.{$name}");

        return $name;
    }

    /**创建用户某个币种的账户
     *
     * @param User $user
     * @param Currency $currency
     *
     * @return mixed
     */
    public static function generateUserAccount($user, $currency)
    {
        //找出所有账户类型,创建中心化钱包
        foreach (self::cursor() as $accountType) {
        
            //账户存在则不创建
            $exists = self::getAccountClass($accountType->id)->where('user_id', $user->id)
                ->where('currency_id', $currency->id)
                ->exists();
           
            if ($exists) {
                continue;
            }

            self::getAccountClass($accountType->id)->create([
                'user_id'         => $user->id,
                'account_type_id' => $accountType->id,
                'currency_id'     => $currency->id,
            ]);
        }

        return $user;
    }

    /**生成用户所有币种的账户
     *
     * @param $user
     *
     * @return mixed
     */
    public static function generateUserAllAccount($user)
    {
     

        foreach (Currency::cursor() as $currency) {
            $user = self::generateUserAccount($user, $currency);
        }
        return $user;
    }

    /**获取一个账户类
     *
     * @param int $account_type_id
     * @param array $attributes
     *
     * @return Account|ChangeAccount|LegalAccount|LeverAccount
     */
    public static function getAccountClass($account_type_id, $attributes = [])
    {
        $class = self::ACCOUNT_CLASSES[$account_type_id];
               
        return new $class($attributes);
    }
}
