<?php


namespace App\Models\Chain;

use App\Models\Account\Account;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\Model;

/**链上协议
 * Class ChainProtocol
 *
 * @package App\Models
 */
class ChainProtocol extends Model
{
    public $timestamps = false;

    protected $casts = [
        'data' => 'array'
    ];

    public function wallets()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->hasMany(Currency::class);
    }

    public function currencyProtocol()
    {
        return $this->belongsTo(CurrencyProtocol::class, 'id', 'chain_protocol_id');
    }

    public function currencyProtocols()
    {
        return $this->hasMany(CurrencyProtocol::class);
    }
}
