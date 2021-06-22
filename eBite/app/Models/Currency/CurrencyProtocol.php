<?php


namespace App\Models\Currency;

use App\Models\Chain\ChainProtocol;
use App\Models\Chain\ChainWallet;
use App\Models\Model;

class CurrencyProtocol extends Model
{
    public $timestamps = false;

    protected $appends = [

    ];

    protected $hidden = [
        'out_private_key'
    ];

    public function setOutPrivateKeyAttribute($out_private_key)
    {
        $out_private_key = encrypt($out_private_key);
        $this->attributes['out_private_key'] = $out_private_key;
    }

    public function getRealOutPrivateKeyAttribute()
    {
        $out_private_key = $this->getAttribute('out_private_key');
        $out_private_key = decrypt($out_private_key);
        return $out_private_key;
    }

    public function getChainProtocolCodeAttribute()
    {
        return $this->chainProtocol->code ?? __('model.未知');
    }

    public function getCurrencyCodeAttribute()
    {
        return $this->currency->code ?? __('model.未知');
    }

    public function chainProtocol()
    {
        return $this->belongsTo(ChainProtocol::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function wallets(){
        return $this->hasMany(ChainWallet::class);
    }
}
