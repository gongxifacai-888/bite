<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ThrowException;
use App\Logic\GoChain;
use App\Models\Setting\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Account\{Account, AccountLog, AccountType, AccountDraw, ChangeAccount, TransferredLog};
use App\Models\Lever\LeverTransaction;
use App\Models\User\User;
use App\Models\Currency\Currency;
use Illuminate\Support\Facades\Response;

class AccountController extends Controller
{
    /**
     * 划转
     *
     * @param Request $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function transfer(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $currency_id = $request->get('currency_id', '');
                $balance     = $request->get('balance', 0);
                $from        = $request->get('from', '');
                $to          = $request->get('to', '');
                $user_id     = User::getUserId();
                if (!is_numeric($balance) || $balance <= 0) {
                    throw new \Exception(__('api.数量错误'));
                }
                if ($from == "" || $to == "") {
                    throw new \Exception(__('api.划转账户类型不能为空'));
                }
                if ($from == $to) {
                    throw new \Exception(__('api.划转账户类型不能相同'));
                }
                $currency          = Currency::findOrFail($currency_id);
                $from_account_type = AccountType::where('account_code', $from)->firstOrFail();
                $to_account_type   = AccountType::where('account_code', $to)->firstOrFail();
                // 查询对应币种所支持的账户类型
                $account_types          = $currency->account_types;
                $currency_account_codes = $account_types->pluck('account_code')->all();
                // 判断用户传入的账户类型是否多于该币种所支持的账户类型
                $diff = array_diff([$from, $to], $currency_account_codes);
                if (count($diff) > 0) {
                    throw new  \Exception(__("api.所选转户类型不是当前币种有效类型"));
                }
                // 查找中间账户
                $center_account_type = AccountType::where('is_recharge', 1)->firstOrFail();
                if (!in_array($center_account_type->account_code, [$from, $to])) {
                    throw new \Exception(__('api.划转账户类型不在有效范围'));
                }
                if ($from == 'lever_account') {
                    $lever = LeverTransaction::where('status', LeverTransaction::STATUS_CLOSING)->where('user_id', $user_id)->first();
                    if ($lever) {
                        return $this->error(__('api.有正在平仓记录，禁止划转'));
                    }
                }
                $extra_data['strict'] = true;
                // 从账户划出
                Account::getAccountByType($from_account_type->id, $currency_id, $user_id)
                    ->changeBalance(AccountLog::ACCOUNT_TRANSFER_OUT, -$balance, $extra_data);
                // 从账户划入
                Account::getAccountByType($to_account_type->id, $currency_id, $user_id)
                    ->changeBalance(AccountLog::ACCOUNT_TRANSFER_IN, $balance, $extra_data);
                if ($from == 'financial_account') {
                    $fee = bc($balance, '*', Setting::getValueByKey('current_transfer_fee', 0.01));
                    Account::getAccountByType($to_account_type->id, $currency_id, $user_id)
                        ->changeBalance(AccountLog::CURRENT_TRANSFER_FEE, -$fee);
                }
                // 插入记录
                TransferredLog::create([
                    'user_id'     => $user_id,
                    'currency_id' => $currency_id,
                    'balance'     => $balance,
                    'from'        => $from,
                    'to'          => $to,
                ]);
            });
            return $this->success(__('api.操作成功'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return $this->error(__('[:model_name] model instance not found', ['model_name' => $ex->getModel()]));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //划转日志
    public function transferLog(Request $request)
    {
        $type        = $request->get('type', 'legal_account');
        $limit       = $request->get('limit', 20);
        $currency_id = $request->get('currency_id', '');
        $user_id     = User::getUserId();
        if (empty($currency_id)) {
            return $this->error(__('api.参数错误'));
        }
        $account_type = AccountType::orderBy('id', 'desc')->pluck('account_code')->all();
        if (!in_array($type, $account_type)) {
            return $this->success(__('api.类型错误'));

        }
        $log = TransferredLog::Where('user_id', $user_id)
            ->where('currency_id', $currency_id)
            ->where(function ($query) use ($type) {
                if (!empty($type)) {
                    $query->where('from', $type)->orWhere('to', $type);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success(__('api.成功'), $log);
    }

    public function hasLeverTrade($user_id)
    {
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)
            ->whereNotIn('status', [LeverTransaction::STATUS_CLOSED, LeverTransaction::STATUS_CANCEL])
            ->count();
        return $exist_close_trade > 0 ? true : false;
    }


    /**
     * 钱包详情
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $id              = request('id', 0);
        $currency_id     = request('currency_id', 0);
        $account_type_id = request('account_type_id', 0);

        $class = AccountType::getAccountClass($account_type_id);

        $account = $class::with(['currency' => function ($query) {
            $query->with(['currencyProtocols' => function ($query) {
                $query->with('chainProtocol:id,code');
            }]);
        }])->where('user_id', User::getUserId())
            ->when($currency_id, function ($query, $currency_id) {
                $query->where('currency_id', $currency_id);
            })->when($id, function ($query, $id) {
                $query->where('id', $id);
            })->first();

        if (!$account) {
            return $this->error(__('api.找不到账户'));
        }

        $account->currency->append('account_types', 'is_recharge_account');
        $account->append('convert_cny', 'convert_usd', 'account_type');

        return $this->success(__('api.请求成功'), $account);
    }

    /**我的资产
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $account_types = AccountType::where('is_display', AccountType::STATUS_ON)->get();


        /**@var AccountType|Collection $account_types * */
        $account_types->transform(function ($accountType) {
            /**@var AccountType $accountType * */
            /**@var Account $accounts * */
            $accounts                 = $accountType->getUserAccounts(User::getUserId());
            $accountType->convert_usd = 0;
            $accountType->convert_cny = 0;
            $accounts->each(function ($account) use ($accountType) {
                $accountType->convert_usd = bc($accountType->convert_usd, '+', $account->convert_usd);
                $accountType->convert_cny = bc($accountType->convert_cny, '+', $account->convert_cny);
                $accountType->convert_usd = parse_price($accountType->convert_usd);
                $accountType->convert_cny = parse_price($accountType->convert_cny);
            });
            $accountType->accounts = $accounts;
            $accountType->addHidden('classname');
            return $accountType;
        });

        return $this->success(__('api.请求成功'), $account_types);
    }

    /**申请提币
     *
     * @return Response
     * @throws ThrowException
     */
  public function draw()
    {
      return transaction(function () {
        $user_id              = User::getUserId();
        $currency_id          = request("currency_id", '');
        $number               = request("number", 0);
        $address              = request("address", '');
        $memo                 = request("memo", '');
        $currency_protocol_id = request("currency_protocol_id", 0);

        $currency = Currency::findOrFail($currency_id);

        if (empty($currency_id) || empty($number) || empty($address) || empty($currency_protocol_id)) {
            return $this->error(__('api.参数错误'));
        }
        if ($number <= 0) {
            return $this->error(__('api.输入的提币数量不能为零或负数'));
        }
        // if ($number < $currency->draw_min) {
        //     return $this->error(__('api.提币数量不能少于最小值'));
        // }
        if ($number > $currency->draw_max) {
            return $this->error(__('api.提币数量不能高于最大值'));
        }

        //这里可以动态指定提币账户
        $account_type_id = AccountType::CHANGE_ACCOUNT_ID;
        $class           = AccountType::getAccountClass($account_type_id);

        $account = $class::getAccountForLock($currency->id);

        if ($account->currency->open_draw == Currency::OFF) {
            return $this->error(__('api.此币种提币暂未开放'));
        }

        $account->changeBalance(AccountLog::WITHDRAW, -$number);
        $account->changeLockBalance(AccountLog::WITHDRAW, $number);

        $rate = $currency->draw_rate;

        $accountDraw = AccountDraw::create([
            'currency_id'          => $currency_id,
            'user_id'              => $user_id,
            'address'              => $address,
            'memo'                 => $memo,
            'currency_protocol_id' => $currency_protocol_id,
            'account_type_id'      => $account_type_id,
            'number'               => $number,
            'fee'                  => bc($number, '*', $rate),
            'real_number'          => bc($number, '-', bc($number, '*', $rate)),
        ]);
        //   dd(2222);
       // GoChain::submitUserWithdraw($accountDraw);
        return $this->success(__('api.提币申请已成功，等待审核'));
      });
    }
    /**
     * 兑换币
     *
     * @return Response
     * @throws ThrowException
     */
    public function convert()
    {
        return transaction(function () {
            $user_id              = User::getUserId();
            $currency_id          = request("currency_id", '');
            $number               = request("number", 0);
            $currency_protocol_id = request("currency_protocol_id", 0);

            if (empty($currency_id) || empty($number) || empty($currency_protocol_id)) {
                return $this->error(__('api.参数错误'));
            }
            if ($number <= 0) {
                return $this->error(__('api.输入的提币数量不能为零或负数'));
            }

            $currency = Currency::findOrFail($currency_id);

            $usdt_currency = Currency::findOrFail(2);
            // 根据当前价格计算要兑换的USDT数量
            $usdt_number = $currency->usd_price * $number;

            //这里可以动态指定提币账户
            $account_type_id = AccountType::CHANGE_ACCOUNT_ID;
            $class           = AccountType::getAccountClass($account_type_id);

            $account = $class::getAccountForLock($currency->id);
            $account->changeBalance(AccountLog::CONVERT, -$number);

            $account = $class::getAccountForLock($usdt_currency->id);
            $account->changeBalance(AccountLog::CONVERT, $usdt_number);

            // GoChain::submitUserWithdraw($accountDraw);

            return $this->success(__('api.兑换成功'));
        });
    }
    /**我的提币列表
     *
     */
    public function drawList()
    {
        $limit     = request('limit', 10);
        $draw_list = AccountDraw::where('user_id', User::getUserId())->paginate($limit);
        return $this->success(__('api.请求成功'), $draw_list);
    }
}
