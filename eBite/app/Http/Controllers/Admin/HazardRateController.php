<?php

namespace App\Http\Controllers\Admin;

use App\Events\Market\KlineEvent;
use App\Logic\Market;
use App\Logic\SocketPusher;
use App\Models\Account\LeverAccount;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyMatch;
use App\Models\Currency\CurrencyQuotation;
use App\Models\Lever\LeverTransaction;
use App\Models\Account\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ThrowException;

/**合约风险率控制器
 * Class HazardRateController
 *
 * @package App\Http\Controllers\Admin
 */
class HazardRateController extends Controller
{
    public function index()
    {
        $currencies = Currency::where('is_quote', 1)->get();
        return view('admin.lever.hazard.index')->with('currencies', $currencies);
    }

    public function handle()
    {
        $id = request('id', 0);
        $lever_trade = LeverTransaction::find($id);
        return view('admin.lever.hazard.handle')
            ->with('lever_trade', $lever_trade);
    }

    public function postHandle(Request $request)
    {
        try {
            $trade_id = $request->input('id', 0);
            $update_price = $request->input('update_price', 0);
            $write_market = $request->input('write_market', 0);
            if ($trade_id <= 0 || $update_price <= 0) {
                throw new \Exception('参数不合法');
            }
            $trade = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)->find($trade_id);
            if (!$trade) {
                throw new \Exception('交易不存在或已平仓');
            }
            $match = CurrencyMatch::find($trade->currency_match_id);
            Market::handleKline($match->id,$match->symbol, $update_price, 0, $match->market_from);
            return $this->success('向系统发送价格成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $quote_currency_id = $request->input('quote_currency_id', -1);
        $type = $request->input('type', -1);
        $operate = $request->input('operate', -1);
        $hazard_rate = $request->input('hazard_rate', 0);
        $user_id = $request->input('user_id', 0);

        $email = request('email', '');
        $mobile = request('mobile', '');
        $uid = request('uid', '');
        $user_hazard = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
            ->where(function ($query) use ($user_id, $type, $quote_currency_id) {
                !empty($user_id) && $query->where('user_id', $user_id);
                ($type != -1 && in_array($type, [1, 2])) && $query->where('type', $type);
                $quote_currency_id != -1 && $query->where('quote_currency_id', $quote_currency_id);
            })
            ->when($email, function ($query, $email) {
                $query->whereHas('user', function ($query) use ($email) {
                    $query->where('email', $email);
                });
            })->when($mobile, function ($query, $mobile) {
                $query->whereHas('user', function ($query) use ($mobile) {
                    $query->where('mobile', $mobile);
                });
            })->when($uid, function ($query, $uid) {
                $query->whereHas('user', function ($query) use ($uid) {
                    $query->where('email', $uid);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $user_hazard->getCollection();
        $items->transform(function ($item, $key) use ($quote_currency_id) {
            $user_wallet = LeverAccount::where('currency_id', $quote_currency_id)
                ->where('user_id', $item->user_id)
                ->first();
            $item->setAppends(['symbol', 'type_name', 'email', 'mobile', 'uid', 'profits']);
            $hazard_rate = LeverTransaction::getAccountHazardRate($user_wallet);
            $balance = $user_wallet->lever_balance ?? 0;
            $item->setAttribute('lever_balance', $balance)
                ->setAttribute('hazard_rate', $hazard_rate);
            return $item;
        });
        if ($operate != -1 && !empty($hazard_rate)) {
            switch ($operate) {
                case 1:
                    $operate_symbol = '>=';
                    break;
                case 2:
                    $operate_symbol = '<=';
                    break;
                default:
                    $operate_symbol = null;
                    break;
            }
            $items = $items->where('hazard_rate', $operate_symbol, $hazard_rate);
        }
        $user_hazard->setCollection($items);
        return $this->layuiPageData($user_hazard);
    }

    public function total()
    {
        $currencies = Currency::where('is_quote', 1)->get();
        return view('admin.lever.hazard.total')
            ->with('currencies', $currencies);
    }

    public function totalLists(Request $request)
    {
        $email = request('email', '');
        $mobile = request('mobile', '');
        $uid = request('uid', '');
        $limit = $request->input('limit', 10);
        $quote_currency_id = $request->input('quote_currency_id', 0);
        $type = $request->input('type', 0);
        $operate = $request->input('operate', -1);
        $hazard_rate = $request->input('hazard_rate', 0);
        /*
        SELECT
            `user_id`,
            SUM((case `type` when 1 then update_price-price when 2 then price-update_price END)) AS profits_total,
            SUM(caution_money) AS caution_money_total
        FROM lever_transaction
        WHERE `status`=0
        GROUP BY user_id
         */
        $user_hazard = LeverTransaction::where('status', LeverTransaction::STATUS_TRANSACTION)
            ->where(function ($query) use ($type, $quote_currency_id) {
                ($type != -1 && in_array($type, [1, 2])) && $query->where('type', $type);
                $quote_currency_id != -1 && $query->where('quote_currency_id', $quote_currency_id);
            })->when($email, function ($query, $email) {
                $query->whereHas('user', function ($query) use ($email) {
                    $query->where('email', $email);
                });
            })->when($mobile, function ($query, $mobile) {
                $query->whereHas('user', function ($query) use ($mobile) {
                    $query->where('mobile', $mobile);
                });
            })->when($uid, function ($query, $uid) {
                $query->whereHas('user', function ($query) use ($uid) {
                    $query->where('email', $uid);
                });
            })
            ->select(['currency_match_id', 'user_id'])
            ->selectRaw('SUM((CASE `type` WHEN 1 THEN `update_price` - `price` WHEN 2 THEN `price` - `update_price` END)) AS `profits_total`')
            ->selectRaw('SUM(`caution_money`) AS `caution_money_total`')
            ->groupBy('user_id')
            ->paginate($limit);
        // var_dump($user_hazard);exit();
        $items = $user_hazard->getCollection();
        $items->transform(function ($item, $key) use ($quote_currency_id) {
            $user_wallet = LeverAccount::where('currency_id', $quote_currency_id)
                ->where('user_id', $item->user_id)
                ->first();
            $item->setAppends(['mobile', 'email','uid']);
            $hazard_rate = LeverTransaction::getAccountHazardRate($user_wallet);
            $balance = $user_wallet->lever_balance ?? 0;
            $item->setAttribute('lever_balance', $balance)
                ->setAttribute('hazard_rate', floatval($hazard_rate));
            return $item;
        });
        if ($operate != -1 && !empty($hazard_rate)) {
            switch ($operate) {
                case 1:
                    $operate_symbol = '>=';
                    break;
                case 2:
                    $operate_symbol = '<=';
                    break;
                default:
                    $operate_symbol = null;
                    break;
            }
            $items = $items->where('hazard_rate', $operate_symbol, $hazard_rate);
        }
        $user_hazard->setCollection($items);
        return $this->layuiPageData($user_hazard);
    }
}
