<?php


namespace App\Models\Chain;

use App\BlockChain\BlockChain;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\Model;
use App\Models\User\User;
use function GuzzleHttp\Psr7\uri_for;
use App\Exceptions\ThrowException;

/**链上钱包表
 * Class OnlineWallet
 *
 * @property CurrencyProtocol $currencyProtocol
 * @property ChainProtocol    $chainProtocol
 *
 * @package App\Models
 */
class ChainWallet extends Model
{

    protected $hidden = [
        'private_key'
    ];

    /*********************************************************获取器区*****************************************/

    public function getCurrencyCodeAttribute()
    {
        return $this->currency->code ?? __('model.未知');
    }

    public function getUidAttribute()
    {
        return $this->user->uid ?? __('model.未知');
    }

    public function getChainProtocolCodeAttribute()
    {
        return $this->chainProtocol->code ?? __('model.未知');
    }

    /**如果有memo,充币应该充到总账号,返回总账号
     *
     * @return mixed
     */
    public function getAddressAttribute($address)
    {
        if ($this->memo) {
            return $this->currencyProtocol()->value('in_address');
        }
        return $address;
    }


    /*********************************************************关联区*****************************************/

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function chainProtocol()
    {
        return $this->belongsTo(ChainProtocol::class);
    }

    public function currencyProtocol()
    {
        return $this->belongsTo(CurrencyProtocol::class);
    }

    /*********************************************************自定义方法区*****************************************/

    /**
     * @throws \Exception
     */
    public function refreshOnlineBalance()
    {
        $balance = BlockChain::newInstance($this->currencyProtocol)
            ->balance($this->address);
        $this->update([
            'balance' => $balance
        ]);
    }

    /**
     * 归拢
     *
     * @throws \Exception
     */
    public function collect()
    {
        $currencyProtocol = $this->currencyProtocol;
        $instance = BlockChain::newInstance($currencyProtocol);

        if (!$instance::COLLECT) {
            throw new ThrowException('无需归拢');
        }

        if ($this->balance <= 0) {
            throw new ThrowException('余额为0无法归拢');
        }

        $has = TxHash::where('user_id', $this->user_id)
            ->where('currency_id', $this->currency_id)
            ->where('type', TxHash::COLLECT[0])
            ->where('status', TxHash::STATUS_WAIT)
            ->where('currency_protocol_id', $this->currencyProtocol->id)
            ->exists();
        if ($has) {
            throw new ThrowException('请不要重复归拢');
        }
        $balance = $this->balance;
        //如果是归拢公链则需要减去手续费
        if (!$instance::IS_TOKEN) {
            $balance -= $instance->getFeeNumber();
        }
        $tx_hash = $instance->transfer(
            $this->address,
            $this->private_key,
            $this->currencyProtocol->in_address,
            $balance,
            TxHash::COLLECT[0]);
        TxHash::insertHash($this->user_id, $this->currency_id, TxHash::COLLECT,$tx_hash, $this->currencyProtocol->id);
    }

    /**
     * 打入手续费
     *
     * @throws \Throwable
     */
    public function transferFee()
    {
        $currencyProtocol = $this->currencyProtocol;
        $instance = BlockChain::newInstance($currencyProtocol);

        if (!$instance::TRANSFER_FEE) {
            throw new ThrowException('无需手续费');
        }

        if ($this->balance <= 0) {
            throw new ThrowException('链上余额为0无需打入手续费');
        }
        $has = TxHash::where('user_id', $this->user_id)
            ->where('currency_id', $this->currency_id)
            ->where('type', TxHash::FEE[0])
            ->where('status', TxHash::STATUS_WAIT)
            ->where('currency_protocol_id', $this->currencyProtocol->id)
            ->exists();
        if ($has) {
            throw new ThrowException('请不要重复打入');
        }
        $tx_hash = $instance->transferFee(
            $this->currencyProtocol->out_address,
            $this->currencyProtocol->real_out_private_key,
            $this->address,
            $instance->getFeeNumber()
        );
        TxHash::insertHash($this->user_id, $this->currency_id, TxHash::FEE, $tx_hash, $this->currencyProtocol->id);
    }
}
