<?php


namespace App\BlockChain;


use App\Models\Account\Account;
use App\Models\Account\AccountLog;
use App\Models\Account\AccountType;
use App\Models\Chain\ChainProtocol;
use App\Models\Chain\ChainWallet;
use App\Models\Currency\Currency;
use App\Models\Chain\TxHash;
use App\Models\Currency\CurrencyProtocol;
use App\Models\User\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Exceptions\ThrowException;
use mysql_xdevapi\Exception;

abstract class BlockChain
{

    //充值方法
    const API_METHOD = 'apiRecharge';
    const BILL_METHOD = 'billRecharge';

    //是否是代币
    const IS_TOKEN = false;

    //是否需要打入手续费
    const TRANSFER_FEE = true;

    //是否需要归拢
    const COLLECT = true;

    //公链类
    const MAIN_CHAIN_CLASS = self::class;

    /**币种使用的充值方法
     * 供账单充值的计划任务识别
     *
     * @var string
     */
    public $recharge_method;

    protected $address_attribute;
    protected $private_attribute;

    protected static function api()
    {
//        格式
//        $api = [
//            'onlineBalance' => '$HOST/链上余额',
//            'transfer' => '$HOST/转账',
//            'bills' => '$HOST/账单',
//        ];

        $api = [
            'getWalletAddresses' => '$HOST/mch/address/create',
        ];
        return $api;
    }

    //调用接口的场景  1 归拢，2 打入手续费，3 提币

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var CurrencyProtocol
     */
    protected $currencyProtocol;

    /**
     * BlockChain constructor.
     *
     * @param $currencyProtocol
     */


    /**
     * 获取链上余额
     *
     * @param $address
     *
     * @return  float
     *
     * @throws \Exception
     */
    public function balance($address)
    {
        throw new ThrowException('无需刷新链上余额');
    }

    /**生成用户某个钱包
     * 若是此钱包已存在,则不能重复生成
     *
     * @param User  $user
     * @param array $addresses
     *
     * @throws \Exception
     */
    public function generateOnlineWallet($user, $addresses = [])
    {
        $exists = ChainWallet::where('user_id', $user->id)
            ->where('currency_id', $this->currency->id)
            ->where('chain_protocol_id', $this->currencyProtocol->chainProtocol->id)
            ->where('currency_protocol_id', $this->currencyProtocol->id)
            ->exists();
        if ($exists) {
            return;
        }
       // if (!$addresses) {
            $addresses = self::getWalletAddresses($user->id);
      //  }

        $user->chainWallets()->create([
            'currency_id' => $this->currency->id,
            'currency_protocol_id' => $this->currencyProtocol->id,
            'chain_protocol_id' => $this->currencyProtocol->chainProtocol->id,
            'address' => $addresses[$this->address_attribute],
            'private_key' => $addresses[$this->private_attribute],
        ]);
    }

    /**生成memo钱包
     *
     * @param User $user
     */
    public function generateMemoOnlineWallet($user)
    {
        $exists = ChainWallet::where('user_id', $user->id)
            ->where('currency_id', $this->currencyProtocol->currency->id)
            ->where('chain_protocol_id', $this->currencyProtocol->chainProtocol->id)
            ->where('currency_protocol_id', $this->currencyProtocol->id)
            ->exists();
        if ($exists) {
            return;
        }
        $address = $this->currencyProtocol->in_address;
        $memo = $this->generateMemo($user);

        $user->chainWallets()->create([
            'currency_id' => $this->currencyProtocol->currency->id,
            'currency_protocol_id' => $this->currencyProtocol->id,
            'chain_protocol_id' => $this->currencyProtocol->chainProtocol->id,
            'address' => $address,
            'memo' => $memo,
        ]);
    }

    /**
     * 转账
     *
     * @param $from        /出金地址
     * @param $private_key /出金私钥
     * @param $to          /入金地址
     * @param $number      /数量
     * @param $type        /调用接口的场景  1 归拢，2 打入手续费，3 提币
     * @param $extra_data  /扩展数据,比如验证码
     *
     * @return string
     * @throws ThrowException
     */
    public function transfer($from, $private_key, $to, $number, $type, $extra_data = [])
    {
        if ($type == TxHash::WITHDRAW && empty($extra_data['code'])) {
            throw new ThrowException('转账必须提供验证码');
        }
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
     */
    public function transferFee($from, $private_key, $to, $number, $extra_data = [])
    {
        throw new \Exception('无需转入手续费');
    }

    /**
     * 查询账单
     *
     * @param $address
     *
     * @throws \Exception
     */
    public function bills($address)
    {
        throw new ThrowException('查询账单方法未实现');
    }

    /**
     * 获取一个区块链驱动实例
     *
     * @param $currencyProtocol
     *
     * @return static
     * @throws \Exception
     */
    public static function newInstance($currencyProtocol)
    {
        $classname = $currencyProtocol->chainProtocol->classname;
        if (!class_exists($classname)) {
            throw new ThrowException("区块链驱动不存在:{$classname}");
        }
       // dd($classname($currencyProtocol));
        return new $classname($currencyProtocol);
    }

    /**获取api服务器地址
     *
     */
    public static function getNodeServerHost()
    {
        $api = config('go.node_server_host');
        return $api;
    }

    /**获取接口地址
     *
     * @param string $function 要用__FUNCTION__传进来
     *
     * @return string
     */
    public static function getApiUrl($function)
    {
        try {
            $host = self::getNodeServerHost();
            $api = static::api();
            $url = $api[$function];
            $url = str_replace('$HOST', $host, $url);
            return $url;
        } catch (\Throwable $t) {
            throw new ThrowException("获取接口地址失败:{$t->getMessage()}");
        }
    }

    /**
     * 获取交易状态
     *
     * @param $tx_hash
     *
     * @return mixed|void
     * @throws \Exception
     * @throws GuzzleException
     */
    public function txStatus($tx_hash)
    {
        try {
            $url = self::getApiUrl(__FUNCTION__);

            $response = http($url, [
                'hash' => $tx_hash
            ]);

            $code = $response['code'] ?? TxHash::STATUS_FAILED;

            switch ($code) {
                case 0:
                    $status = TxHash::STATUS_SUCCESS;
                    break;
                case 1:
                    $status = TxHash::STATUS_WAIT;
                    break;
                case 2:
                    $status = TxHash::STATUS_FAILED;
                    break;
                case 3:
                    $status = TxHash::STATUS_INVALID;
                    break;
                default:
                    $status = TxHash::STATUS_FAILED;
                    break;
            }

            return $status;

        } catch (\Throwable $t) {
            dump($t->getMessage());
            dump($t->getFile());
            dump($t->getLine());
        }

    }

    /**账单充值
     *
     * @param BlockChain $blockChain
     */
    public function billRecharge($blockChain)
    {

    }

    /**
     * 生成用户所有钱包
     *
     * @param $user
     *
     * @return mixed
     * @throws \Exception
     */
    public static function generateAllOnlineWallet($user,$code,$currency_id)
    {
        //$addresses = self::getWalletAddresses($user->id);
   
        //找出所有币种
        /**@var Currency $currency * */
            $info =new \App\BlockChain\Udun();
           // $currencyProtocol=
            $inoff= $info->getWalletAddresses($user, $code);
   
            $info=$user->chainWallets()->create([
            'currency_id' => $currency_id,
            'address' => $inoff['address'],
            ]);
              //    dd($inoff);
    //     foreach (Currency::cursor() as $currency) {
    //   //     dd($currency);
    //         /**创建去中心化钱包
    //          * 循环币种的所有协议生成
    //          * 不嫩在此处判断是否有钱包
    //          * 如果上币中断需要重新上币,需要在子类判断
    //          */
    //       foreach ($currency->currencyProtocols()->cursor() as $currencyProtocol) {
    //           $blockChain = self::newInstance($currencyProtocol);
    //           //$blockChain=new BlockChain();
    //             $blockChain->generateOnlineWallet($user, $code);
    //         }
    //     }
        return $user;
    }

    /**
     * 获取链上接口的地址信息
     *
     * @param $user_id
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getWalletAddresses($user_id,$code)
    {
        $project_name = config('go.project_name');
       $key=config('go.go_middleware_key');
        try {
            $url = self::getApiUrl(__FUNCTION__);
             $body = array(
                'merchantId' => $project_name,
                'coinType' => $code,
                'callUrl' => 'http://jpmcoinusa.cc/api/block_chain/recharge',
            );
            $body = '['.json_encode($body).']';
            $timestamp = time();
            $nonce = rand(100000, 999999);
  
            $sign = md5($body.$key.$nonce.$timestamp);
            $data = array(
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'sign' => $sign,
                'body' => $body
            );
            $data_string = json_encode($data);
            $response= http_post($url, $data_string);
        //  $response='{"data":{"coinType":520,"address":"0x9a8afc19229fc319bbfd65f85932ca02b893ad03"},"message":"SUCCESS","code":200}';
           $response=json_decode($response,true);
        } catch (GuzzleException $e) {
            throw new ThrowException("请求充币地址失败:{$e->getMessage()}");
        }

       // dd($response);
        // if ($response['code'] != 0) {
        //     throw new ThrowException($response['msg']);
        // }
        return $response['data'];
    }

    /**
     * 生成memo
     *
     * @param int $length
     *
     * @return string
     */
    public function generateMemo($user)
    {
        return $user->uid;
    }

    /**转换放大
     *
     * @param $number
     * @param $decimal
     *
     * @return bool|string|null
     */
    public static function convertUpper($number, $decimal)
    {
        $lesson = bc(10, '^', $decimal);
        return bc($number, '*', $lesson, 0);
    }

    /**转换缩小
     *
     * @param $number
     * @param $decimal
     *
     * @return bool|string|null
     */
    public static function convertLower($number, $decimal)
    {
        $lesson = bc(10, '^', $decimal);
        return bc($number, '/', $lesson);
    }



    /**
     *
     * {
     *      "id":8,
     *      "txid":"0x6bc9dc34797dea25d7e8ffbe53e905bf948efa69f4da43299a5cb67084275fca",
     *      "blocknum":9578963,
     *      "from":"0x7f87D686541B4Ca62CC1b21047cbBF9081612D0D",
     *      "to":"0x2d158dc45accf01daed380335a49603633feec42",
     *      "token":"0xdAC17F958D2ee523a2206206994597C13D831ec7",
     *      "value":"100000182",
     *      "time":"2020-02-29 21:40:56",
     *      "status":0,
     *      "coin":"ERC20",
     *      "index":0
     * }
     *
     */


    /**
     * 解析请求参数并分配驱动
     *
     * @param $data
     *
     * @throws \Exception
     */
    public static function parseApiRechargeData($data)
    {
        // $coin = strtoupper($data['coin']);//充值的什么币的类型
        // $block_no = $data['blocknum'] ?? 0; //区块号
        // $hash = $data['txid'];//交易哈希
        // $balance = $data['value'];//充值量
        // $token_address = $data['token'];//代币地址
        // $index = $data['index'];//充值索引
        // $to = $data['to'];//给谁转的账
        // $time = $data['time'];//转账时间
        // //找到币种协议
        // $currencyProtocol = CurrencyProtocol::when($token_address, function ($query, $token_address) {
        //     $query->where('token_address', $token_address);
        // })->whereHas('chainProtocol', function ($query) use ($coin) {
        //     $query->where('code', $coin);
        // })->first();
        // if (!$currencyProtocol) {
        //     Log::channel('wallet_recharge')->error('找不到此协议', $data);
        //     throw new ThrowException('找不到此协议');
        // }
        // if (TxHash::where('hash', $data['txid'])->where('type', TxHash::FEE[0])->exists()) {
        //     Log::channel('wallet_recharge')->error('充币请求是打入手续费的,不处理', $data);
        //     return;
        // }
        // Log::channel('wallet_recharge')->info('用户充币请求', $data);
       //var_dump($data);die;
       // $data=$data['0'];
        $to=$data['address'];
         $balance=$data['amount']/pow(10,$data['decimals']);
        $hash=$data['txId'];
        $block_no=$data['tradeId'];
        $instance  =new \App\BlockChain\Udun();
        $instance->apiRecharge($to, $balance, $hash, $block_no, $data);
    }

    /**
     * Api充值
     *
     * @param       $address
     * @param       $balance
     * @param       $hash
     * @param       $block_no
     * @param array $full_data
     *
     * @throws \Exception
     */
    public function apiRecharge($address, $balance, $hash, $block_no, $full_data = [])
    {
        // if (!in_array(strtoupper($full_data['coin']), ['BTC', 'OMNI'])) {
        //     $balance = self::convertLower($balance, $this->currencyProtocol->decimal);
        // }
       // $info=Currency::where('number_code',$full_data['coinType'])->first();
           //dd();
        //根据地址查询出链上钱包
        $chainWallet = ChainWallet::where('address', $address)
            //->where('currency_id',$info['id'])
            ->first();
           //  dd($chainWallet);
        if (is_null($chainWallet)) {
            Log::channel('wallet_recharge')->error("找不到钱包:{$address},{11}", $full_data);
            throw new ThrowException("找不到钱包:{$address},{11}");
        }
        if ($balance < 0) {
            Log::channel('wallet_recharge')->error("充值金额错误:{$balance}", $full_data);
            throw new ThrowException("充值金额错误:{$balance}");
        }
        Log::channel('wallet_recharge')->alert("充值金额:{$address},{$balance}", $full_data);
        $account_type_id = AccountType::where('is_recharge', AccountType::IS_RECHARGE)->first();
        if (!$account_type_id) {
            Log::channel('wallet_recharge')->error("找不到账户", $full_data);
            throw new ThrowException("找不到充币账户");
        }
     //   dd($info['id']);
        $account = Account::getAccountByType($account_type_id['id'], $chainWallet->currency_id, $chainWallet->user_id);
        $account->changeBalance(AccountLog::BLOCK_CHAIN_ADD, $balance);

        // TxHash::insertHash($chainWallet->user_id,  $info['id'], TxHash::RECHARGE, $hash,
        //     $this->currencyProtocol->id, TxHash::STATUS_SUCCESS);
    }

    /**获取应该打入多少手续费
     *
     * @return  mixed
     * @throws \Exception
     */
    public function getFeeNumber()
    {
        throw new \Exception('获取手续费方法未实现');
    }
}
