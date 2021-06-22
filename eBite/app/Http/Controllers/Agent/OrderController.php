<?php

namespace App\Http\Controllers\Agent;

use App\Jobs\SettleMicro;
use App\Models\Micro\MicroOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Tx\TxComplete;
use App\Models\Currency\{Currency, CurrencyMatch};
use App\Models\Account\{AccountLog, Account, ChangeAccount};
use App\Models\Agent\{Agent, AgentMoneyLog};
use App\Models\User\User;
use App\Models\Lever\LeverTransaction;
use App\Jobs\SettleLever;
use App\Models\Otc\OtcDetail;
use App\Models\Otc\SellerCurrency;
use App\Models\Tx\TxHistory;

/**
 * 该类处理所有的订单与结算。
 * Class ReportController
 *
 * @package App\Http\Controllers\Agent
 */
class OrderController extends Controller
{

    //合约订单管理
    public function leverIndex()
    {
        $legal_currencies = Currency::where('is_quote', 1)->get();//计价币
        $matches = CurrencyMatch::where('open_lever', 1)->get();//交易对
        return view("agent.order.leverlist", [
            'legal_currencies' => $legal_currencies,
            'matches' => $matches,

        ]);
    }


    //秒合约订单管理
    public function microIndex()
    {
        $matches = CurrencyMatch::where('open_microtrade', CurrencyMatch::ON)->get();//交易对
        return view("agent.order.microlist", [
            'matches' => $matches,
        ]);
    }

    //秒合约订单
    public function microList(Request $request)
    {

        $limit = $request->input("limit", 0);
        $id = $request->input("id", 0);
        $uid = $request->input("uid", '');
        $mobile = $request->input("mobile", '');
        $email = $request->input("email", '');

        $agentusername = $request->input("agentusername", '');
        $status = $request->input("status", 0);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $match_id = $request->input('match_id', 0);

        //当前代理商信息
        $agent_id = Agent::getAgentId();


        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }

        $query = MicroOrder::with('user')->when($mobile, function ($query, $mobile) {
            $user_id = User::where('mobile', $mobile)->value('id');
            $query->where('user_id', $user_id);
        })->when($email, function ($query, $email) {
            $user_id = User::where('email', $email)->value('id');
            $query->where('user_id', $user_id);
        })->when($uid, function ($query, $uid) {
            $user_id = User::where('uid', $uid)->value('id');
            $query->where('user_id', $user_id);
        })->when($id, function ($query, $id) {
            $query->where('id', $id);
        })->when($status, function ($query, $status) {
            $query->where('status', $status);
        })->when($type, function ($query, $type) {
            $query->where('type', $type);
        })->when($start, function ($query, $start) {
            $query->where('created_at', '>=', $start);
        })->when($end, function ($query, $end) {
            $query->where('create_time', '<=', (new Carbon($end))->addDay());
        })->when($match_id, function ($query, $match_id) {
            $query->where('match_id', $match_id);
        })->when($now_agent_id, function ($query, $now_agent_id) {
            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        });

        $order_list = $query->orderByDesc('id')->paginate($limit);
        $items = $order_list->getCollection();
        $items->transform(function ($item, $key) {
            $item->setAppends(['parent_agent_name', 'user_agent_level', 'mobile', 'email', 'uid', 'symbol']);
            return $item;
        });
        $order_list->setCollection($items);

        $order_total = $query->count();
        $fee = $query->sum('fee');

        $toucun = $query->where('status', MicroOrder::STATUS_CLOSED)->sum('fact_profits');
        //查询当前代理商的头寸  手续费百分比
        $now_agent = Agent::getAgentById($now_agent_id);
        $toucun = -bc($toucun, '*', $now_agent->pro_loss / 100); // 乘以代理商头寸百分比 取负数
        $fee = bc($fee, '*', $now_agent->pro_ser / 100,4); // 乘以代理商头寸百分比 取负数

        return $this->layuiPageData($order_list, '', [
            'order_total' => $order_total,
            'toucun' => $toucun,
            'fee' => $fee,
        ]);
    }

    //撮合完成单
    public function transactionIndex()
    {
        $currency_matches = CurrencyMatch::where('open_change', 1)->get();

        return view("agent.order.transaction", [
            'currency_matches' => $currency_matches
        ]);
    }

    //撮合订单
    public function transactionList(Request $request)
    {
        $limit = $request->get('limit', 10);
        //当前代理商信息
        $agent_id = Agent::getAgentId();
        $node_users = User::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();

        $account_number = $request->get('account_number', '');


        $result = TxComplete::when($account_number, function ($query, $account_number) {
            $query->whereHas('inUser', function ($query) use ($account_number) {
                $query->where('email', $account_number)->orWhere('mobile', $account_number);
            })->orWhereHas('outUser', function ($query) use ($account_number) {
                $query->where('email', $account_number)->orWhere('mobile', $account_number);
            });
        })->where(function ($query) use ($request) {
            $currency_match_id = $request->input('currency_match_id', '');
            $currency_match_id > 0 && $query->where('currency_match_id', $currency_match_id);
            $start_time = $request->input('start_time', '');
            $end_time = $request->input('end_time', '');
            if (!empty($start_time)) {
                $query->where('created_at', '>=', $start_time);
            }
            if (!empty($end_time)) {
                $query->where('created_at', '<=', $end_time);
            }
        })->where(function ($query) use ($node_users) {
            $query->where(function ($query) use ($node_users) {
                $query->whereIn('in_user_id', $node_users);
            })->orwhere(function ($query) use ($node_users) {
                $query->whereIn('out_user_id', $node_users);
            });

        })->orderBy('id', 'desc')->paginate($limit);
        $sum = $result->sum('number');
        return $this->layuiPageData($result, '成功', $sum);
    }


    //合约
    public function orderList(Request $request)
    {

        $limit = $request->input("limit", 10);
        $id = $request->input("id", 0);
        $uid = $request->input("uid", '');
        $mobile = $request->input("mobile", '');
        $email = $request->input("email", '');

        $agentusername = $request->input("agentusername", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $legal_id = $request->input("legal_id", -1);
        $match_id = $request->input('match_id', 0);

        //当前代理商信息
        $agent_id = Agent::getAgentId();


        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }

        $quote_currency_id = 0;
        $base_currency_id = 0;
        if ($match_id > 0) {
            $match = CurrencyMatch::find($match_id);
            $quote_currency_id = $match->quote_currency_id ?? 0;
            $base_currency_id = $match->base_currency_id ?? 0;
        }

        $query = LeverTransaction::whereHas('user', function ($query) use ($mobile, $email, $uid) {

            $mobile != '' && $query->where('mobile', $mobile);
            $email != '' && $query->where('email', $email);
            $uid != '' && $query->where('uid', $uid);

        })->where(function ($query) use ($id, $status, $type) {

            $id != 0 && $query->where('id', $id);

            $status != 10 && in_array($status, [LeverTransaction::STATUS_ENTRUST, LeverTransaction::STATUS_TRANSACTION, LeverTransaction::STATUS_CLOSED, LeverTransaction::STATUS_CANCEL, LeverTransaction::STATUS_CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        })->when($quote_currency_id > 0, function ($query) use ($quote_currency_id) {
            $query->where('quote_currency_id', $quote_currency_id);
        })->when($base_currency_id > 0, function ($query) use ($base_currency_id) {
            $query->where('base_currency_id', $base_currency_id);
        })->where(function ($query) use ($now_agent_id) {

            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        });

        $order_list = $query->orderBy('id', 'desc')->paginate($limit);

        $items = $order_list->getCollection();
        $items->transform(function ($item, $key) {

            $item->setAppends(['parent_agent_name', 'user_agent_level', 'mobile', 'email', 'uid', 'symbol', 'time']);

            return $item;
        });
        $order_list->setCollection($items);


        return $this->layuiPageData($order_list);
    }


    /**
     *获取合约统计数据
     */
    public function getOrderAccount(Request $request)
    {

        $id = $request->input("id", 0);
        $uid = $request->input("uid", '');
        $mobile = $request->input("mobile", '');
        $email = $request->input("email", '');

        $agentusername = $request->input("agentusername", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');

        $legal_id = $request->input("legal_id", -1);
        $match_id = $request->input('match_id', 0);
        //当前代理商信息
        $agent_id = Agent::getAgentId();


        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }

        $lever = LeverTransaction::whereHas('user', function ($query) use ($uid, $mobile, $email) {

            $uid != '' && $query->where('uid', $uid);
            $mobile != '' && $query->where('mobile', $mobile);
            $email != '' && $query->where('email', $email);

        })->where(function ($query) use ($id, $status, $type) {

            $id != 0 && $query->where('id', $id);

            $status != 10 && in_array($status, [LeverTransaction::STATUS_ENTRUST, LeverTransaction::STATUS_TRANSACTION, LeverTransaction::STATUS_CLOSED, LeverTransaction::STATUS_CANCEL, LeverTransaction::STATUS_CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        })->where(function ($query) use ($now_agent_id) {

            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        })->when($match_id > 0, function ($query, $match_id) {
            $query->where('match_id', $match_id);
        });

        //可用保证金 未平仓
        $_lock = $lever->selectRaw('sum(if(status <= 2,caution_money,0)) as caution_money')->value('caution_money') ?? 0;

        //总订单数
        $_count = $lever->count();

        //头寸收益（平仓最终盈亏）
        $_toucun = $lever->whereIn('status', [LeverTransaction::STATUS_CLOSED])->sum('fact_profits');
        //手续费收益（已平仓手续费）
        $_shouxu = $lever->whereIn('status', [LeverTransaction::STATUS_CLOSED])->sum('trade_fee');

        //查询当前代理商的头寸  手续费百分比
        $now_agent = Agent::getAgentById($now_agent_id);

        $data = [];
        $data['_num'] = $_count;

        $data['_toucun'] = -bc($_toucun, '*', $now_agent->pro_loss / 100,4); // 乘以代理商头寸百分比 取负数
        $data['_shouxu'] = bc($_shouxu, '*', $now_agent->pro_ser / 100,4); // 乘以代理商手续费百分比

        $_all = bc($data['_toucun'], '+', $data['_shouxu'],4);

        $data['_all'] = $_all;
        //可用保证金
        $data['_lock'] = $_lock;


        return $this->ajaxReturn($data);
    }



    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */

    //导出订单记录Excel
    public function orderExcel(Request $request)
    {
        $limit = $request->input("limit", 10);
        $id = $request->input("id", 0);
        $username = $request->input("username", '');
        $agentusername = $request->input("agentusername", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $legal_id = $request->input("legal_id", -1);
        $match_id = $request->input('match_id', 0);

        //当前代理商信息
        $agent_id = Agent::getAgentId();


        if ($agentusername != '') {

            $search_agent = Agent::where('username', $agentusername)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }

            $parent_agent = explode(',', $search_agent->agent_path);

            if (!in_array($agent_id, $parent_agent)) {
                return $this->error('该代理商并不属于您的团队');
            }

            $now_agent_id = $search_agent->id;
        } else {
            $now_agent_id = $agent_id;
        }

        $quote_currency_id = 0;
        $base_currency_id = 0;
        if ($match_id > 0) {
            $match = CurrencyMatch::find($match_id);
            $quote_currency_id = $match->quote_currency_id ?? 0;
            $base_currency_id = $match->base_currency_id ?? 0;
        }

        $query = LeverTransaction::whereHas('user', function ($query) use ($username) {
            $username != '' && $query->where('mobile', $username)->orWhere('email', $username);
        })->where(function ($query) use ($id, $status, $type) {

            $id != 0 && $query->where('id', $id);

            $status != 10 && in_array($status, [LeverTransaction::STATUS_ENTRUST, LeverTransaction::STATUS_TRANSACTION, LeverTransaction::STATUS_CLOSED, LeverTransaction::STATUS_CANCEL, LeverTransaction::STATUS_CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        })->when($quote_currency_id > 0, function ($query) use ($quote_currency_id) {
            $query->where('quote_currency_id', $quote_currency_id);
        })->when($base_currency_id > 0, function ($query) use ($base_currency_id) {
            $query->where('base_currency_id', $base_currency_id);
        })->where(function ($query) use ($now_agent_id) {

            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        });


        $order_list = $query->orderBy('id', 'desc')->get()->toArray();

        $data = $order_list;
        //dd($data);
        return Excel::create('订单数据', function ($excel) use ($data) {
            $excel->sheet('订单数据', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('用户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('所属代理商');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('用户等级');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('交易类型');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('交易对');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('当前状态');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('原始价格');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('开仓价格');
                });
                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('当前价格');
                });
                $sheet->cell('K1', function ($cell) {
                    $cell->setValue('最终盈亏');
                });
                $sheet->cell('L1', function ($cell) {
                    $cell->setValue('手数');
                });
                $sheet->cell('M1', function ($cell) {
                    $cell->setValue('倍数');
                });
                $sheet->cell('N1', function ($cell) {
                    $cell->setValue('初始保证金');
                });
                $sheet->cell('O1', function ($cell) {
                    $cell->setValue('当前可用保证金');
                });
                $sheet->cell('P1', function ($cell) {
                    $cell->setValue('创建时间');
                });
                $sheet->cell('Q1', function ($cell) {
                    $cell->setValue('完成时间');
                });
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if ($value['type'] == 1) {
                            $value['type'] = "买入";
                        } else {
                            $value['type'] = "卖出";
                        }
                        if ($value['status'] == 0) {
                            $value['status'] = "挂单中";
                        } elseif ($value['status'] == 1) {
                            $value['status'] = "交易中";
                        } elseif ($value['status'] == 2) {
                            $value['status'] = "平仓中";
                        } elseif ($value['status'] == 3) {
                            $value['status'] = "已平仓";
                        } elseif ($value['status'] == 4) {
                            $value['status'] = "已撤单";
                        }

                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['user_name']);
                        $sheet->cell('C' . $i, $value['parent_agent_name']);
                        $sheet->cell('D' . $i, $value['agent_level']);
                        $sheet->cell('E' . $i, $value['type']);
                        $sheet->cell('F' . $i, $value['symbol']);

                        $sheet->cell('G' . $i, $value['status']);
                        $sheet->cell('H' . $i, $value['origin_price']);
                        $sheet->cell('I' . $i, $value['price']);
                        $sheet->cell('J' . $i, $value['update_price']); //当前价格
                        $sheet->cell('K' . $i, $value['fact_profits']);

                        $sheet->cell('L' . $i, $value['share']); //手数
                        $sheet->cell('M' . $i, $value['multiple']);
                        $sheet->cell('N' . $i, $value['origin_caution_money']); //初始保证金
                        $sheet->cell('O' . $i, $value['caution_money']);
                        $sheet->cell('P' . $i, $value['create_time']); //创建时间

                        $sheet->cell('Q' . $i, $value['complete_time']);
                    }
                }
                ob_end_clean();
            });
        })->download('xlsx');
    }


    //导出用户记录Excel
    public function user_excel(Request $request)
    {

        $id = request()->input('id', 0);
        $parent_id = request()->input('parent_id', 0);
        $account_number = request()->input('account_number', '');
        $start = request()->input('start', '');
        $end = request()->input('end', '');

        $users = new User();

        if ($id) {
            $users = $users->where('id', $id);
        }
        if ($parent_id > 0) {
            $users = $users->where('agent_node_id', $parent_id);
        }
        if ($account_number) {
            $users = $users->where(function ($query) use ($account_number) {
                $query->where('mobile', $account_number)->orWhere('email', $account_number);
            });
        }

        if (!empty($start) && !empty($end)) {
            $users->whereBetween('created_at', [$start, $end]);
        }

        $agent_id = Agent::getAgentId();
        $users = $users->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");

        $data = $users->get()->toArray();
        //dd($data);
        return Excel::create('用户列表', function ($excel) use ($data) {
            $excel->sheet('用户列表', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('用户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('用户身份');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('上级代理商');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('手机号');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('邮箱');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('邀请码');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('加入时间');
                });


                if (!empty($data)) {
                    foreach ($data as $key => $value) {

                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account']);
                        $sheet->cell('C' . $i, $value['my_agent_level']);
                        $sheet->cell('D' . $i, $value['parent_name']);
                        $sheet->cell('E' . $i, $value['mobile']);
                        $sheet->cell('F' . $i, $value['email']);
                        $sheet->cell('G' . $i, $value['invite_code']);
                        $sheet->cell('H' . $i, $value['created_at']);
                    }
                }
                ob_end_clean();
            });
        })->download('xlsx');
    }

    /**
     * 结算列表首页
     */
    public function jieIndex(Request $request)
    {
        //计价币
        $legal_currencies = Currency::get();

        $legal_currencies = $legal_currencies->filter(function ($currency) {
            /**@var Currency $currency * */
            if ($currency->micro_account_display) {
                return true;
            }
            if ($currency->is_quote) {
                return true;
            }
            return false;
        });

        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        $self = Agent::getAgent();
        $is_admin = $self ? $self->is_admin : 0;
        return view('agent.order.settle', [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
            'is_admin' => $is_admin
        ]);
    }

    public function jieList(Request $request)
    {

        $limit = $request->input("limit", 10);
        $start = $request->input("start", '');
        $end = $request->input("end", '');

        $agent_id = Agent::getAgentId();
        //$node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $child_agents = Agent::getAllChildAgent($agent_id);
        $son_agents = $child_agents->pluck('id')->all();

        $query = AgentMoneyLog::with(['user'])->whereIn('agent_id', $son_agents)
            ->where(function ($query) use ($request) {

                $id = $request->input("id", 0);
                $mobile = $request->input("mobile", '');
                $email = $request->input("email", '');
                $uid = $request->input("uid", '');
                $belong_agent = $request->input('belong_agent', 0);
                $quote_currency_id = $request->input('quote_currency_id', -1);

                $type = $request->input("type", 0);

                $query->when($id > 0, function ($query) use ($id) {
                    $query->where('id', $id);
                })->whereHas('user', function ($query) use ($mobile, $email, $uid) {
                    $mobile != "" && $query->where('mobile', $mobile);
                    $email != "" && $query->where('email', $email);
                    $uid != "" && $query->where('uid', $uid);

                })->when($belong_agent, function ($query, $belong_agent) {
                    $query->whereHas('agent', function ($query) use ($belong_agent) {
                        $query->where('username', $belong_agent);
                    });
                })->when($quote_currency_id > 0, function ($query) use ($quote_currency_id) {
                    $query->where('quote_currency_id', $quote_currency_id);
                })->when($type, function ($query, $type) {
                    $query->where('type', $type);
                });
            })->where(function ($query) use ($start, $end) {

                !empty($start) && $query->where('created_time', '>=', strtotime($start . ' 0:0:0'));

                !empty($end) && $query->where('created_time', '<=', strtotime($end . ' 23:59:59'));
            });


        $list = $query->orderBy('id', 'desc')->paginate($limit);
        $sum = $query->sum('change');

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }


    //合约订单结算（对账）
    public function dojie(Request $request)
    {

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $belong_agent = $request->input('belong_agent', 0);
        $quote_currency_id = $request->input('quote_currency_id', -1);

        //超级代理 有权限
        $self = Agent::getAgent();
        if ($self->is_admin != 1) {
            return $this->error('只有超级代理才有权限');
        }
        $now_agent_id = $self->id;

        if ($belong_agent != 0) {
            $search_agent = Agent::where('username', $belong_agent)->first();
            if (!$search_agent) {
                return $this->error('代理商不存在');
            }
            $now_agent_id = $search_agent->id;
        }

        $lever_ids = LeverTransaction::where(function ($query) use ($start, $end) {
            //平仓时间
            !empty($start) && $query->where('complete_time', '>=', strtotime($start . ' 0:0:0'));
            !empty($end) && $query->where('complete_time', '<=', strtotime($end . ' 23:59:59'));
        })->where(function ($query) use ($now_agent_id) {
            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        })->when($quote_currency_id > 0, function ($query) use ($quote_currency_id) {
            $query->where('quote_currency_id', $quote_currency_id);
        })->where('status', LeverTransaction::STATUS_CLOSED)
            ->where('settled', LeverTransaction::NOT_SETTLE)
            ->get()->pluck('id')->all();

        $micro_ids = MicroOrder::where(function ($query) use ($start, $end) {
            //平仓时间
            !empty($start) && $query->where('complete_at', '>=', strtotime($start . ' 0:0:0'));
            !empty($end) && $query->where('complete_at', '<=', strtotime($end . ' 23:59:59'));
        })->where(function ($query) use ($now_agent_id) {
            $now_agent_id > 0 && $query->whereRaw("FIND_IN_SET($now_agent_id,`agent_path`)");
        })->when($quote_currency_id > 0, function ($query) use ($quote_currency_id) {
            $query->where('currency_id', $quote_currency_id);
        })->where('status', MicroOrder::STATUS_CLOSED)
            ->where('settled', MicroOrder::NOT_SETTLE)
            ->get()->pluck('id')->all();

        if (!empty($lever_ids)) {
            SettleLever::dispatch($lever_ids);
        }
        if (!empty($micro_ids)) {
            SettleMicro::dispatch($micro_ids);
        }

        return $this->success('正在结算～请稍后刷新页面');
    }

    //结算 提现到账
    public function walletOut(Request $request)
    {
        $id = $request->get('id', 0);
        try {
            DB::beginTransaction();

            $agentLog = AgentMoneyLog::lockForUpdate()->findOrFail($id);
            $agentLog->draw();

            DB::commit();
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }


    /**
     * 订单详情
     */
    public function orderInfo(Request $request)
    {
        $id = $request->input("id", 0);
        $agentMoneyLog = AgentMoneyLog::findOrFail($id);

        if ($agentMoneyLog->type == AgentMoneyLog::LEVER_LOSS || $agentMoneyLog->type == AgentMoneyLog::LEVER_FEE) {
            $order = $agentMoneyLog->leverOrder;
            return view("agent.order.info", ['info' => $order]);
        }
        if ($agentMoneyLog->type == AgentMoneyLog::MICRO_LOSS || $agentMoneyLog->type == AgentMoneyLog::MICRO_FEE) {
            $order = $agentMoneyLog->microOrder;
            return view("agent.order.micro_order_info", ['info' => $order]);
        }
    }


    //我的合约订单
    public function userLeverIndex(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }

        return view("agent.user.leverlist", ['user_id' => $id]);
    }

    public function userLeverList(Request $request)
    {

        $limit = $request->input("limit", 10);

        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $user_id = $request->input("user_id", '');


        $query = LeverTransaction::where('user_id', $user_id)->where(function ($query) use ($status, $type) {


            $status != 10 && in_array($status, [LeverTransaction::STATUS_ENTRUST, LeverTransaction::STATUS_TRANSACTION, LeverTransaction::STATUS_CLOSED, LeverTransaction::STATUS_CANCEL, LeverTransaction::STATUS_CLOSING]) && $query->where('status', $status);

            $type > 0 && in_array($type, [1, 2]) && $query->where('type', $type);
        })->where(function ($query) use ($start, $end) {

            !empty($start) && $query->where('create_time', '>=', strtotime($start . ' 0:0:0'));

            !empty($end) && $query->where('create_time', '<=', strtotime($end . ' 23:59:59'));
        });


        $order_list = $query->orderBy('id', 'desc')->paginate($limit);

        $items = $order_list->getCollection();
        $items->transform(function ($item, $key) {

            $item->setAppends(['mobile', 'uid', 'email', 'parent_agent_name', 'user_agent_level', 'symbol', 'time']);

            return $item;
        });
        $order_list->setCollection($items);

        return $this->layuiPageData($order_list);
    }


    //撮合完成单
    public function transactionHistoryIndex()
    {
        $currency_matches = CurrencyMatch::where('open_change', 1)->get();

        return view("agent.order.transaction_history", [
            'currency_matches' => $currency_matches
        ]);
    }

    //撮合订单
    public function transactionHistoryList(Request $request)
    {
        $limit = $request->get('limit', 10);
        //当前代理商信息
        $agent_id = Agent::getAgentId();
        $node_users = User::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();

        $mobile = $request->get('mobile', '');
        $uid = $request->get('uid', '');
        $email = $request->get('email', '');

        $result = TxHistory::with('currencyMatch')->whereHas('user', function ($query) use ($mobile, $uid, $email) {
            $mobile != "" && $query->where('mobile', $mobile);
            $uid != "" && $query->where('uid', $uid);
            $email != "" && $query->where('email', $email);

        })->where(function ($query) use ($request) {
            $currency_match_id = $request->input('currency_match_id', '');
            $currency_match_id > 0 && $query->where('currency_match_id', $currency_match_id);
            $start_time = $request->input('start_time', '');
            $end_time = $request->input('end_time', '');
            if (!empty($start_time)) {

                $query->where('created_at', '>=', $start_time);
            }
            if (!empty($end_time)) {

                $query->where('created_at', '<=', $end_time);
            }
        })->where(function ($query) use ($node_users) {
            $query->where(function ($query) use ($node_users) {
                $query->whereIn('user_id', $node_users);
            });

        })->orderBy('id', 'desc')->paginate($limit);
        $sum = $result->sum('number');
        return $this->layuiPageData($result, '成功', $sum);
    }

    //otc订单管理
    public function otcIndex()
    {
        $currencies = SellerCurrency::getLegalCurrencies();
        return view("agent.order.otclist", [
            'currencies' => $currencies,
        ]);
    }


    public function detailList(Request $request)
    {
        $status = $request->get('status', -1);
        $limit = $request->get('limit', 20);
        $way = $request->get('way', '');
        $buy_account = $request->get('buy_account', '');
        $sell_account = $request->get('sell_account', '');
        $currency_id = $request->get('currency_id', -1);
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $buy_mobile = $request->get('buy_mobile', '');
        $sell_mobile = $request->get('sell_mobile', '');
        $buy_email = $request->get('buy_email', '');
        $sell_email = $request->get('sell_email', '');
        $agent_id = Agent::getAgentId();
        $node_users = User::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $list = OtcDetail::when($status != -1, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($currency_id > 0, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->when($way != '', function ($query) use ($way) {
            $query->where('way', $way);
        })->when($buy_account != '', function ($query) use ($buy_account) {
            $query->whereHas('buyUser', function ($query) use ($buy_account) {
                $query->where('uid', $buy_account);
            });
        })->when($sell_account != '', function ($query) use ($sell_account) {
            $query->whereHas('sellUser', function ($query) use ($sell_account) {
                $query->where('uid', $sell_account);
            });
        })->when($buy_mobile != '', function ($query) use ($buy_mobile) {
            $query->whereHas('sellUser', function ($query) use ($buy_mobile) {
                $query->where('mobile', $buy_mobile);
            });
        })->when($sell_mobile != '', function ($query) use ($sell_mobile) {
            $query->whereHas('buyUser', function ($query) use ($sell_mobile) {
                $query->where('mobile', $sell_mobile);
            });
        })->when($buy_email != '', function ($query) use ($buy_email) {
            $query->whereHas('sellUser', function ($query) use ($buy_email) {
                $query->where('uid', $buy_email);
            });
        })->when($sell_email != '', function ($query) use ($sell_email) {
            $query->whereHas('sellUser', function ($query) use ($sell_email) {
                $query->where('uid', $sell_email);
            });
        })->when($start_time != '', function ($query) use ($start_time) {
            $query->where('created_at', '>=', $start_time);
        })->when($end_time != '', function ($query) use ($end_time) {
            $query->where('created_at', '<=', $end_time);
        })->where(function ($query) use ($node_users) {
            $query->where(function ($query) use ($node_users) {
                $query->whereIn('buy_user_id', $node_users);
            })->orWhere(function ($query) use ($node_users) {
                $query->whereIn('seller_user_id', $node_users);
            });

        })->orderBy('id', 'desc')->paginate($limit);

        $list->getCollection()->each(function ($item) {
            $item->setAppends(['currency_name', 'buy_user_account', 'sell_user_account']);
        });


        return $this->layuiPageData($list);
    }
}
