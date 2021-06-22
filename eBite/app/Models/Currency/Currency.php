<?php


namespace App\Models\Currency;

use App\Models\Account\Account;
use App\Models\Account\AccountType;
use App\Models\Chain\ChainProtocol;
use App\Models\Chain\ChainWallet;
use App\Models\Micro\MicroNumber;
use App\Models\Model;

/**
 * Class Currency
 *
 *
 * @property bool $micro_account_display
 * @property bool $change_account_display
 * @property bool $lever_account_display
 * @property bool $legal_account_display
 *
 * @package App\Models\Currency
 */
class Currency extends Model
{
    const ON = 1;
    const OFF = 0;

    protected $casts = [
        'accounts_display' => 'array'
    ];

    protected $appends = [
        'micro_account_display',
        'change_account_display',
        'lever_account_display',
        'legal_account_display',
    ];


    /**判断是否秒合约账户显示
     *
     */
    public function getMicroAccountDisplayAttribute()
    {
        $displays = $this->getAttribute('accounts_display') ?? [];
        return in_array(AccountType::MICRO_ACCOUNT_ID, $displays);
    }

    /**判断是否币币账户显示
     *
     */
    public function getChangeAccountDisplayAttribute()
    {
        $displays = $this->getAttribute('accounts_display') ?? [];
        return in_array(AccountType::CHANGE_ACCOUNT_ID, $displays);
    }

    /**判断是否合约账户显示
     *
     */
    public function getLeverAccountDisplayAttribute()
    {
        $displays = $this->getAttribute('accounts_display') ?? [];
        return in_array(AccountType::LEVER_ACCOUNT_ID, $displays);
    }

    /**判断是否法币账户显示
     *
     */
    public function getLegalAccountDisplayAttribute()
    {
        $displays = $this->getAttribute('accounts_display') ?? [];
        return in_array(AccountType::LEGAL_ACCOUNT_ID, $displays);
    }

    public function matches()
    {
        return $this->hasMany(CurrencyMatch::class, 'quote_currency_id');
    }

    public function wallets()
    {
        return $this->hasMany(ChainWallet::class);
    }

    public function currencyProtocols()
    {
        return $this->hasMany(CurrencyProtocol::class);
    }

    public function microNumbers()
    {
        return $this->hasMany(MicroNumber::class, 'currency_id', 'id');
    }

    public function getAccountTypesAttribute()
    {
        $accounts_display = $this->accounts_display ?? [];
        return AccountType::whereIn('id', $accounts_display)->where('is_display', AccountType::STATUS_ON)->get();
    }

    public function getIsRechargeAccountAttribute()
    {
        return $this->account_types->where('is_recharge', 1)->count() > 0;
    }
}
