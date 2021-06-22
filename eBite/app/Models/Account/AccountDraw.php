<?php

namespace App\Models\Account;

use App\BlockChain\BlockChain;
use App\Models\Chain\TxHash;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\Model;
use App\Models\User\User;

class AccountDraw extends Model
{

    const STATUS_WAIT = 1;  //默认状态 待审核
    const STATUS_CONFIRM = 2;    //已汇出
    const STATUS_FIELD = 3;   //汇出失败
    const STATUS_SUCCESS = 4;    //成功
    const STATUS_REJECT = 5;   //已驳回

    protected $appends = ['status_name'];

    public function getReviewAtAttribute($key)
    {
        if ($key > 0) {
            return date('Y-m-d H:i:s', $key);
        }
        return __('model.未审核');
    }

    public function getStatusNameAttribute()
    {
        $arr = [
            self::STATUS_FIELD => __('model.汇出失败'),
            self::STATUS_WAIT => __('model.待审核'),
            self::STATUS_CONFIRM => __('model.已汇出'),
            self::STATUS_REJECT => __('model.已驳回'),
            self::STATUS_SUCCESS => __('model.成功'),
        ];
        return $arr[$this->getAttribute('status')] ?? __('model.未知');
    }

    public function getTxHashAttribute()
    {
        return $this->txHash->hash;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function currencyProtocol()
    {
        return $this->belongsTo(CurrencyProtocol::class);
    }

    public function txHash()
    {
        return $this->belongsTo(TxHash::class)->withDefault([
            'hash' => __('model.暂无')
        ]);
    }

    /**标记汇出失败
     *
     */
    public function failed()
    {
        $this->status = AccountDraw::STATUS_FIELD;
        $this->save();
        return $this;
    }

    /**标记成功
     *
     */
    public function success()
    {
        $this->status = AccountDraw::STATUS_SUCCESS;
        $this->save();
        return $this;
    }

    /**标记确认
     *
     * @throws \App\Exceptions\ThrowException
     * @throws \Exception
     */
    public function confirm($use_chain_api, $notes, $code)
    {
        if ($use_chain_api == 1) {
            //使用链上接口 获取驱动并执行转账  返回值 交易哈希
            $blockChain = BlockChain::newInstance($this->currencyProtocol);

            $out_address = $this->currencyProtocol->out_address;
            $private_key = $this->currencyProtocol->real_out_private_key;

            if (!$out_address) {
                throw new \Exception('请设置总账号');
            }
            if (!$private_key) {
                throw new \Exception('请设置总账号私钥');
            }

            $number = $this->real_number;
            $address = $this->address;
            $type = TxHash::WITHDRAW;
            $extra_data = [
                'code' => $code,
                'memo' => $this->memo,
            ];

            $out_hash = $blockChain->transfer($out_address, $private_key, $address, $number, $type[0], $extra_data);
            $tx_id = TxHash::insertHash($this->user_id, $this->currency_id, $type, $out_hash, $this->currency_protocol_id);
            //写入哈希
            $this->tx_hash_id = $tx_id;
            $this->status = AccountDraw::STATUS_CONFIRM;
        } else {
            $this->notes = $notes;
            $this->status = AccountDraw::STATUS_SUCCESS;
        }
        //扣除锁定余额
        $account = Account::getAccountByType($this->account_type_id, $this->currency_id, $this->user_id);
        $account->changeLockBalance(AccountLog::WITHDRAW_CONFIRM, -$this->number);

        $this->review_at = time();
        //写入哈希
        $this->save();

        return $this;
    }

    /**标记驳回
     *
     * @return $this
     * @throws \Exception
     */
    public function reject()
    {
        $this->status = AccountDraw::STATUS_REJECT;
        $this->review_at = time();
        $this->save();

        $account = Account::getAccountByType($this->account_type_id, $this->currency_id, $this->user_id);
        $account->changeLockBalance(AccountLog::WITHDRAW_BACK, -$this->number);
        $account->changeBalance(AccountLog::WITHDRAW_BACK, $this->number);

        return $this;
    }


}
