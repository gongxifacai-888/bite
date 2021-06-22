<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ThrowException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Account\{Account, AccountType};
use App\Models\Currency\{Currency, CurrencyMatch};
use App\Models\Micro\{MicroSecond, MicroOrder};
use App\Models\User\User;
use App\Logic\MicroTrade;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,authorization,Authorization");
class MicroOrderController extends Controller
{

    /**
     * 取允许支付的币种
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayableCurrencies()
    {
        $currencies = Currency::with(['microNumbers' => function ($query) {
            $query->orderBy('number');
        }])->get()->filter(function ($currency, $key) {
            return $currency->micro_account_display;
        })->values();

        $currencies = $currencies->transform(function ($currency, $key) {
            // 追加上用户的钱包
            try {
                $account = Account::getAccountByType(AccountType::MICRO_ACCOUNT_ID, $currency->id);
                $currency->setAttribute('micro_account', $account ?? null);
                return $currency;
            } catch (ThrowException $e) {

            }
        })->filter(function ($currency) {
            return !is_null($currency);
        });
        return $this->success('', $currencies);
    }

    /**
     * 取到期时间
     */
    public function getSeconds()
    {
        $seconds = MicroSecond::where('status', MicroSecond::STATUS_ON)
            ->orderBy('seconds')->get();
        return $this->success('', $seconds);
    }

    /**
     * 下单
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        try {
            $user_id = User::getUserId();
            $type = $request->input('type', 0);
            $match_id = $request->input('match_id', 0);
            $currency_id = $request->input('currency_id', 0);
            $seconds = $request->input('seconds', 0);
            $number = $request->input('number', 0);
            $validator = Validator::make($request->all(), [
                'match_id' => 'required|integer|min:1',
                'currency_id' => 'required|integer|min:1',
                'type' => 'required|integer|in:1,2',
                'seconds' => 'required',
                'number' => 'required',
                // 'seconds' => 'required|integer|min:1',
                // 'number' => 'required|integer|min:0',
            ], [], [
                'match_id' => '交易对',
                'currency_id' => '支付币种',
                'type' => '下单类型',
                'seconds' => '到期时间',
                'number' => '投资数额',
            ]);
            //进行基本验证
            throw_if($validator->fails(), new \Exception($validator->errors()->first()));
            $currency = Currency::find($currency_id);
            throw_unless($currency->micro_account_display, new \Exception(__('api.当前币种账户不支持此交易')));
            $currencyMatch = CurrencyMatch::find($match_id);
            throw_unless($currencyMatch->open_microtrade, new \Exception(__('api.当前交易对不支持此交易')));
            $close = $currencyMatch->getLastPrice();
            $price = $close;
            $order_data = [
                'user_id' => $user_id,
                'type' => $type,
                'match_id' => $match_id,
                'currency_id' => $currency_id,
                'seconds' => $seconds,
                'price' => $price,
                'number' => $number,
            ];
            $order = MicroTrade::addOrder($order_data);
            return $this->success(__('api.下单成功'), $order);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function lists(Request $request)
    {
        try {
            $user_id = User::getUserId();
            $limit = $request->input('limit', 10);
            $status = $request->input('status', -1);
            $match_id = $request->input('match_id', -1);
            $currency_id = $request->input('currency_id', -1);
            $lists = MicroOrder::where('user_id', $user_id)
                ->when($status <> -1, function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->when($match_id <> -1, function ($query) use ($match_id) {
                    $query->where('match_id', $match_id);
                })
                ->when($currency_id <> -1, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                })
                ->orderBy('id', 'desc')
                ->paginate($limit);
            $lists->each(function ($item, $key) {
                return $item->append('remain_milli_seconds');
            });
            /*
            $results = $lists->getCollection();
            $results->transform(function ($item, $key) {
                return $item->append('remain_milli_seconds');
            });
            $lists->setCollection($results);
            */
            return $this->success('', $lists);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 获得该币种交易中的秒合约订单
     */
    protected function getExistingOrderNumber($user_id, $currency_id)
    {
        $count = MicroOrder::where('user_id', $user_id)
            ->where('status', MicroOrder::STATUS_OPENED)
            ->where('currency_id', $currency_id)
            ->count();
        return $count;
    }
}
