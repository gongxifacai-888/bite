<?php


namespace App\BlockChain;

use App\Models\Account\AccountLog;
use App\Models\Chain\TxHash;
use App\Models\Account\Account;
use App\Models\Currency\Currency;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exceptions\ThrowException;


class ERC20 extends BlockChain
{

    const IS_TOKEN = true;

    //公链类
    const MAIN_CHAIN_CLASS = ETH::class;

    protected $address_attribute = 'erc20_address';
    protected $private_attribute = 'erc20_private';

    public $recharge_method = self::API_METHOD;

    protected static function api()
    {
        $parent_api = parent::api();
        $api = [
            'transfer' => '$HOST/v3/wallet/eth/tokensendto',
            'txStatus' => '$HOST/wallet/eth/tx',
            'balance' => '$HOST/wallet/eth/tokenbalance',
        ];

        return array_merge($api, $parent_api);
    }

    /**
     * 刷新余额
     *
     * @param $address
     *
     * @return bool|float|string|null
     * @throws GuzzleException
     * @throws ThrowException
     */
    public function balance($address)
    {
        $url = $this->getApiUrl(__FUNCTION__);
        $response = http($url, [
            'address' => $address,
            'tokenaddress' => $this->currencyProtocol->token_address
        ]);
        if ($response['code'] != 0) {
            throw new ThrowException($response['msg']);
        }
        $balance = $this->convertLower($response['data']['balance'] ?? 0,
            $this->currencyProtocol->decimal);
        return $balance;
    }

    /**
     * 转账
     *
     * @param        $from_address
     * @param        $private_key
     * @param        $to_address
     * @param        $number
     * @param        $type
     * @param string $code
     *
     * @return mixed
     * @throws \Throwable
     * @throws GuzzleException
     */
    public function transfer($from, $private_key, $to, $number, $type, $extra_data = [])
    {
        parent::transfer($from, $private_key, $to, $number, $type, $extra_data);
        $amount = self::convertUpper($number, $this->currencyProtocol->decimal);
        $gas_price = $this->currencyProtocol->chainProtocol->data['gas_price'] ?? false;
        $gas_limit = $this->currencyProtocol->chainProtocol->data['gas_limit'] ?? false;
        if (!$gas_price || !$gas_limit) {
            throw new \Exception('请去区块链协议设置此协议的手续费');
        }
        $url = self::getApiUrl(__FUNCTION__);
        $response = http($url, [
            'fromaddress' => $from,
            'privkey' => $private_key,
            'toaddress' => $to,
            'tokenaddress' => $this->currencyProtocol->token_address,
            'amount' => $amount,
            'type' => $type,
            'gas_price' => $gas_price,
            'gas_limit' => $gas_limit,
            'verificationcode' => $extra_data['code'] ?? ''
        ], 'POST');
        if ($response['code'] != 0) {
            throw new ThrowException($response['msg']);
        }
        return $response['data']['txHex'];
    }

    /**
     * 转手续费
     *
     * @param $from
     * @param $private_key
     * @param $to
     *
     * @return mixed
     * @throws ThrowException
     * @throws GuzzleException
     * @throws \Throwable
     */
    public function transferFee($from, $private_key, $to, $number, $extra_data = [])
    {
        $eth = Currency::where('code', 'ETH')->first();
        if (!$eth) {
            throw new \Exception('ERC20转手续费需要依赖ETH币种');
        }
        $instance = self::newInstance($eth->currencyProtocols()->first());
        return $instance->transfer($from, $private_key, $to, $number, TxHash::FEE[0], $extra_data);
    }

    /**
     *
     */
    public function getFeeNumber()
    {
        $gas_price = $this->currencyProtocol->chainProtocol->data['gas_price'] ?? false;
        $gas_limit = $this->currencyProtocol->chainProtocol->data['gas_limit'] ?? false;
        if (!$gas_price || !$gas_limit) {
            throw new \Exception('请去区块链协议设置此协议的手续费');
        }
        $number = $gas_price * $gas_limit;

        $fee = self::convertLower($number, 18);
        return $fee;
    }
}
