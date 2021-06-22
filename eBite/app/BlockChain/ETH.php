<?php


namespace App\BlockChain;


use App\Models\Account\AccountLog;
use App\Models\Currency\CurrencyQuotation;
use App\Models\Chain\TxHash;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Utils\BC;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exceptions\ThrowException;

class ETH extends BlockChain
{
    protected $address_attribute = 'eth_address';
    protected $private_attribute = 'eth_private';
    public    $recharge_method   = self::API_METHOD;

    protected static function api()
    {
        $parent_api = parent::api();
        $api = [
            'txStatus' => '$HOST/wallet/eth/tx',
            'balance'  => '$HOST/wallet/eth/balance',
            'transfer' => '$HOST/v3/wallet/eth/sendto',
            'bills'    => 'http://api.etherscan.io/api',
        ];
        return array_merge($api, $parent_api);
    }

    /**
     * 获取链上余额并刷新
     *
     * @param $address
     *
     * @return float|int|mixed|string|null
     * @throws GuzzleException
     * @throws ThrowException
     */
    public function balance($address)
    {
        $url = self::getApiUrl(__FUNCTION__);
        $response = http($url, [
            'address' => $address,
        ]);
        if ($response['code'] != 0) {
            throw new ThrowException($response['msg']);
        }
        $balance = $this->convertLower($response['data']['balance'] ?? 0, 18);
        return $balance;
    }

    /**
     *ETH转账
     *
     * @param        $from
     * @param        $private_key
     * @param        $to
     * @param        $number
     * @param        $type /调用接口的场景  1 归拢，2 打入手续费，3 提币
     * @param string $code
     *
     * @return mixed
     * @throws GuzzleException
     * @throws ThrowException
     */
    public function transfer($from, $private_key, $to, $number, $type, $extra_data = [])
    {
        parent::transfer($from, $private_key, $to, $number, $type, $extra_data);
        $amount = self::convertUpper($number, $this->currencyProtocol->decimal);

        $gas_price = $this->currencyProtocol->chainProtocol->data['gas_price'] ?? false;
        $gas_limit = $this->currencyProtocol->chainProtocol->data['gas_limit'] ?? false;
        if (!$gas_price || !$gas_limit) {
            throw new ThrowException('请去区块链协议设置此协议的手续费');
        }
        $url = self::getApiUrl(__FUNCTION__);
        $params = [
            'fromaddress'      => $from,
            'privkey'          => $private_key,
            'toaddress'        => $to,
            'amount'           => $amount,
            'type'             => $type,
            'verificationcode' => $extra_data['code'] ?? '',
            'gas_price'        => $gas_price,
            'gas_limit'        => $gas_limit,
            'tokenaddress'     => $this->currencyProtocol->token_address,
        ];
        array_push($params, $url);
        $response = http($url, $params, 'POST');
        if ($response['code'] != 0) {
            throw new ThrowException("节点报错:{$response['msg']}");
        }
        return $response['data']['txid'];
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
