<?php

namespace App\Logic;

use App\BlockChain\BlockChain;
use App\Models\Account\AccountDraw;
use function GuzzleHttp\json_encode;

class GoChain
{
    private static $goChainKey = null;

    private static function getGoChainKey()
    {
        if (empty(self::$goChainKey)) {
            self::$goChainKey = config('go.go_middleware_key');
            if (self::$goChainKey == '') {
                throw new \Exception('GO_MIDDLEWARE_KEY未设置');
            }
            self::$goChainKey = decrypt(decrypt(self::$goChainKey)); //解密两次
        }
        return self::$goChainKey;
    }

    /**
     * 生成签名
     *
     * @return array
     */
    public static function makeSign($json_str)
    {
        return md5($json_str . self::getGoChainKey());
    }

    /**
     * 打包转换要发送的数据
     *
     * @param array $params
     *
     * @return void
     */
    public static function pack(&$params)
    {
        if (isset($params['sign'])) {
            unset($params['sign']);
        }
        if (!isset($params['t'])) {
            $params['t'] = time();
        }
        ksort($params, SORT_STRING);
        $json_str = json_encode($params);
        $sign = self::makeSign($json_str);
        $params = [
            'data' => $json_str,
            'sign' => $sign,
        ];
    }

    /**
     * 发起一个请求
     *
     * @param string  $method 请求类型
     * @param string  $uri
     * @param array   $params
     * @param string  $params_name
     * @param boolean $need_to_sign
     *
     * @return array
     */
    public static function request($method, $uri, $params, $params_name, $need_to_sign = false)
    {
        $client = app('GoMiddlewareClient');
        $need_to_sign && self::pack($params);
        $params = [
            $params_name => $params,
        ];
        $response = $client->request($method, $uri, $params);
        $contents = $response->getBody()->getContents();
        return json_decode($contents, true);
    }

    /**
     * 同步用户
     *
     * @param \App\Models\User\User $user 用户模型实例
     *
     * @return array
     */
    public static function syncUserInfo($user)
    {
        $uri = '/walletMiddle/AddUser';
        $method = 'POST';
        $params_name = 'form_params';
        $params = [
            'user_id' => $user->id,
            'phone' => $user->getOriginal('mobile', ''),
            'email' => $user->getOriginal('email', ''),
            'country_code' => $user->area->global_code,
        ];
        $result = self::request($method, $uri, $params, $params_name, true);
        return $result;
    }

    /**
     * 查询钱包
     *
     * @param \App\Users    $user     用户模型实例
     * @param \App\Currency $currency 币种模型
     *
     * @return array
     */
    public static function getWalletAddress($user, $currency)
    {
        $uri = "/walletMiddle/GetDrawAddress";
        $method = 'GET';
        $params_name = 'query';
        $params = [
            'user_id' => $user->id,
            'coin_type' => $currency->type,
        ];
        $result = self::request($method, $uri, $params, $params_name);
        return $result;
    }

    /**
     * 发送验证码
     *
     * @param \App\Users    $user
     * @param \App\Currency $currency
     *
     * @return array
     */
    public static function getVerificationcode($user)
    {
        $uri = "/walletMiddle/SendVerificationcode";
        $method = 'GET';
        $params_name = 'query';
        $params = [
            'user_id' => $user->id,
        ];
        $result = self::request($method, $uri, $params, $params_name);
        return $result;
    }

    /**
     * 绑定提币地址
     *
     * @param \App\Users    $user     用户模型实例
     * @param \App\Currency $currency 币种名称
     * @param string        $address  提币地址
     * @param string        $code     验证码
     *
     * @return array
     */
    public static function bindWithdrawAddress($user, $currency, $address, $verificationcode)
    {
        $uri = "/walletMiddle/BindDrawAddress";
        $method = 'POST';
        $params_name = 'form_params';
        $params = [
            'user_id' => $user->id,
            'coin_name' => $currency->code,
            'address' => $address,
            'verificationcode' => $verificationcode,
            'contract_address' => $currency->token_address,
        ];
        $result = self::request($method, $uri, $params, $params_name, true);
        return $result;
    }

    /**
     * 获取用户的提币地址
     *
     * @param \App\Users    $user
     * @param \App\Currency $currency
     *
     * @return array
     */
    public static function getBindWithdrawAddress($user, $currency)
    {
        $uri = "/walletMiddle/GetDrawAddress";
        $method = 'GET';
        $params_name = 'query';
        $params = [
            'user_id' => $user->id,
            'coin_name' => $currency->code,
            'contract_address' => $currency->token_address,
        ];
        $result = self::request($method, $uri, $params, $params_name, true);
        return $result;
    }

    /**
     * 用户发起提币
     *
     * @param AccountDraw $accountDraw 提币模型实例
     *
     * @return array
     */
    public static function submitUserWithdraw($accountDraw)
    {
        $currency = $accountDraw->currency;
        $uri = "/walletMiddle/SubmitUserDrawInfo";
        $method = 'POST';
        $params_name = 'form_params';
        $number = BlockChain::convertUpper($accountDraw->real_number, $accountDraw->currencyProtocol->decimal);
        $params = [
            'id' => $accountDraw->id,
            'user_id' => $accountDraw->user_id,
            'coin_name' => $accountDraw->currencyProtocol->chainProtocol->code,
            'coin_type' => $currency->code,
            'number' => $number,
            'address' => $accountDraw->address,
            'contract_address' => $accountDraw->currencyProtocol->token_address,
            'memo' => $accountDraw->memo,
        ];
        $result = self::request($method, $uri, $params, $params_name, true);

        if (!isset($result['code']) || $result['code'] != 0) {
            throw new \Exception($result['errorinfo'] ?? '中间件未知错误');
        }

        return $result;
    }

    /**
     * 审核用户提币
     *
     * @param \App\UsersWalletOut $wallet_out 提币模型实例
     *
     * @return array
     */
    public static function auditUserWithdraw($wallet_out)
    {
        $currency = $wallet_out->currencyCoin;
        $uri = "/walletMiddle/AuditUserDrawInfo";
        $fee = $currency->chain_fee;
        $ratio = bc(10, '^', $currency->decimal_scale);
        $number = bc($wallet_out->real_number, '*', $ratio, 0);
        $chain_fee = bc($fee, '*', $ratio, 0);
        $method = 'POST';
        $params_name = 'form_params';
        $params = [
            'id' => $wallet_out->id,
            'user_id' => $wallet_out->user_id,
            'coin_name' => $currency->code,
            'number' => $number,
            'address' => $wallet_out->address,
            'fromaddress' => $currency->total_account,
            'privkey' => $currency->origin_key,
            'contract_address' => $currency->token_address,
            'verificationcode' => $wallet_out->verificationcode,
            'fee' => $chain_fee, //放大后的手续费
        ];
        $result = self::request($method, $uri, $params, $params_name, true);
        return $result;
    }

    /**更新出金地址
     *
     * @return array
     */
    public static function updateOutAddress($coin, $chain, $address, $code)
    {
        $uri = '/v3/wallet/changeoutaddress';

        $projectname = config('go.project_name');

        $params = [
            'projectname' => $projectname,
            'coin' => $chain,//接口里的币要求是链的名称不是币的名称
            'address' => $address,
            'verificationcode' => $code,
        ];


        $client = app('NodeClient');
        $params = [
            'form_params' => $params,
        ];
        $response = $client->request('POST', $uri, $params);
        $contents = $response->getBody()->getContents();
        return json_decode($contents, true);
    }

    /**更新入金地址
     *
     * @return array
     */
    public static function updateInAddress($coin, $chain, $address, $code)
    {
        $uri = '/v3/wallet/changeinaddress';

        $projectname = config('go.project_name');

        $params = [
            'projectname' => $projectname,
            'coin' => $chain,//接口里的币要求是链的名称不是币的名称
            'address' => $address,
            'verificationcode' => $code,
        ];

        $client = app('NodeClient');
        $params = [
            'form_params' => $params,
        ];
        $response = $client->request('POST', $uri, $params);
        $contents = $response->getBody()->getContents();
        return json_decode($contents, true);
    }


}
