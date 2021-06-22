<?php

namespace App\Http\Controllers\Admin;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Micro\{MicroNumber, MicroOrder, MicroSecond};
use App\Models\Currency\{Currency, CurrencyMatch};

class MicroController extends Controller
{
    public function index()
    {
        return view('admin.micro.index');
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new MicroNumber();
        } else {
            $result = MicroNumber::find($id);
        }
        $currencies = Currency::get()->filter(function ($currency) {
            /**@var $currency Currency* */
            return $currency->micro_account_display;
        });

        return view('admin.micro.add')->with('result', $result)->with('currencies', $currencies);
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $currency_id = $request->get('currency_id', '');
        $number = $request->get('number', '');

        if (empty($id)) {
            $micro_number = new MicroNumber();
        } else {
            $micro_number = MicroNumber::find($id);
            if ($micro_number == null) {
                return redirect()->back();
            }
        }
        $micro_number->currency_id = $currency_id;
        $micro_number->number = $number;

        DB::beginTransaction();
        try {
            $micro_number->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new MicroNumber();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($result);
    }

    public function del(Request $request)
    {
        $id = $request->get('id', 0);
        $nicro_number = MicroNumber::find($id);
        if (empty($nicro_number)) {
            return $this->error('参数错误');
        }
        try {
            $nicro_number->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    //micro_seconds
    public function secondsIndex()
    {
        return view('admin.micro.seconds_index');
    }

    public function secondsAdd(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new MicroSecond();
        } else {
            $result = MicroSecond::find($id);
        }
        return view('admin.micro.seconds_add')
            ->with('result', $result);
    }

    public function secondsPostAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $seconds = $request->get('seconds', '');
        $status = $request->get('status', '');
        $profit_ratio = $request->get('profit_ratio', '');

        if ($seconds < 10) {
            return $this->error('秒数最小不能小于10秒');
        }

        if (empty($id)) {
            $result = new MicroSecond();
        } else {
            $result = MicroSecond::find($id);
            if ($result == null) {
                return redirect()->back();
            }
        }
        $result->seconds = $seconds;
        $result->profit_ratio = $profit_ratio;
        $result->status = $status;

        DB::beginTransaction();
        try {
            $result->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function secondsLists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new MicroSecond();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($result);
    }

    public function secondsDel(Request $request)
    {
        $id = $request->get('id', 0);
        $result = MicroSecond::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        try {
            $result->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function secondsStatus(Request $request)
    {
        $id = $request->get('id', 0);
        $result = MicroSecond::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        if ($result->status == MicroSecond::STATUS_ON) {
            $result->status = MicroSecond::STATUS_OFF;
        } else {
            $result->status = MicroSecond::STATUS_ON;
        }
        try {
            $result->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function order()
    {
        $currencies = Currency::get()->filter(function ($item, $key) {
            return $item->micro_account_display;
        })->values();
        $currency_matches = CurrencyMatch::where('open_microtrade', CurrencyMatch::ON)->get();
        return view('admin.micro.orders')
            ->with('currencies', $currencies)
            ->with('currency_matches', $currency_matches);
    }

    public function orderList(Request $request)
    {
        $currency_id = $request->input('currency_id', -1);
        $match_id = $request->input('match_id', -1);
        $type = $request->input('type', -1);
        $mobile = $request->input('mobile', '');
        $account = $request->input('account', '');
        $email = $request->input('email', '');
        $limit = $request->input('limit', 10);
        $status = $request->input('status', -1);
        $start = $request->input("start_time", '');
        $end = $request->input("end_time", '');
        $pre_profit_result = $request->input('pre_profit_result', -2);
        $profit_result = $request->input('profit_result', -2);

        $results = MicroOrder::with(['currency', 'currencyMatch', 'user'])
            ->when($currency_id != -1, function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })->when($match_id != -1, function ($query) use ($match_id) {
                $query->where('match_id', $match_id);
            })->when($type != -1, function ($query) use ($type) {
                $query->where('type', $type);
            })->when($status != -1, function ($query) use ($status) {
                $query->where('status', $status);
            })->when($pre_profit_result != -2, function ($query) use ($pre_profit_result) {
                $query->where('pre_profit_result', $pre_profit_result);
            })->when($profit_result != -2, function ($query) use ($profit_result) {
                $query->where('profit_result', $profit_result);
            })->when($account != '', function ($query) use ($account) {
                $query->whereHas('user', function ($query) use ($account) {
                    // $account != '' && $query->where("mobile", $account)->orwhere('email', $account);
                    $query->where('uid', $account);
                });
            })->when($email, function ($query, $email) {
                $query->whereHas('user', function ($query) use ($email) {
                    $query->where('email', $email);
                });
            })->when($mobile, function ($query, $mobile) {
                $query->whereHas('user', function ($query) use ($mobile) {
                    $query->where('mobile', $mobile);
                });
            })->when($start != '', function ($query) use ($start) {
                $query->where('created_at', '>=', $start);
            })->when($end != '', function ($query) use ($end) {
                $query->where('created_at', '<=', $end);
            })->orderBy('id', 'desc')
            ->paginate($limit);
        $items = $results->getCollection();
        $items->transform(function ($item, $key) {
            return $item->append('pre_profit_result_name')->makeVisible('pre_profit_result');
        });
        $results->setCollection($items);
        return $this->layuiPageData($results);
    }

    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $order = MicroOrder::findOrFail($id);
        if ($order->status != MicroOrder::STATUS_OPENED) {
            abort(403,'订单不是交易中,无法继续操作');
        }
        return view('admin.micro.edit', ['result' => $order]);
    }

    /**给秒合约订单设置单控
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editPost()
    {
        $risk = request('risk', 0);
        $id = request("id", 0);

        $order = MicroOrder::findOrFail($id);
        if (!$order) {
            return $this->error("找不到单子");
        }
        if ($order->status != MicroOrder::STATUS_OPENED) {
            return $this->error("订单不是交易中,无法修改");
        }

        $has_order = MicroOrder::where('match_id', $order->match_id)
            ->where('status', MicroOrder::STATUS_OPENED)
            ->where('pre_profit_result', '<>', 0)
            ->where('handled_at', $order->handled_at)
            ->where('id', '<>', $id)->exists();

        if ($has_order) {
            return $this->error("已存在同交易对的同平仓时间的单子被风控,不能继续设置");
        }


        $order->pre_profit_result = $risk;
        $order->save();
        return $this->success('编辑成功');
    }

    public function batchRisk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $risk = $request->input('risk', 0);
            if (empty($ids)) {
                throw new \Exception('请先选择要处理的交易');
            }
            if (!in_array($risk, [-1, 0, 1])) {
                throw new \Exception('风控类型不正确');
            }
            $affect_rows = MicroOrder::where('status', MicroOrder::STATUS_OPENED)
                ->whereIn('id', $ids)
                ->update([
                    'pre_profit_result' => $risk,
                ]);
            return $this->success('本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
    public function usersIndex()
    {
        return view('admin.micro.users');
    }

    public function usersList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        $risk = $request->get('risk', -2);
        $list = User::when($account != '', function ($query) use ($account) {
            $query->where("mobile", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%');
        })->when($risk != -2, function ($query) use ($risk) {
            $query->where('risk', $risk);
        })->orderBy('id', 'desc')
            ->paginate($limit);
        $list->each(function ($item, $key) {
            $item->setHidden([]);
            return $item->append(['risk_name']);
        });
        return $this->layuiPageData($list);
    }

    public function batchUsersRisk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $risk = $request->input('risk', 0);
            throw_if(
                empty($ids),
                new \Exception('请先选择用户')
            );
            throw_if(
                !in_array($risk, [-1, 0, 1]),
                new \Exception('风控类型不正确')
            );
            $affect_rows = User::whereIn('id', $ids)
                ->update([
                    'risk' => $risk,
                ]);
            return $this->success('本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
