<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account\{AccountLog,
    ChangeAccount,
    ChangeAccountLog,
    AccountDraw,
    AccountType,
    LeverAccount,
    MicroAccount};
use App\Models\Agent\Agent;
use App\Models\User\User;
use App\Models\Lever\LeverTransaction;
use App\Models\Currency\Currency;

class CapitalController extends Controller
{

    //充币
    public function rechargeIndex()
    {
        //充币币种
        $legal_currencies = Currency::get()->filter(function($item,$key){
            return $item->change_account_display;
        })->values();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        return view("agent.capital.recharge", [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
        ]);
    }

    //提币
    public function withdrawIndex()
    {
        //币种
        $legal_currencies = Currency::get()->filter(function($item,$key){
            return $item->change_account_display;
        })->values();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        return view("agent.capital.withdraw", [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
        ]);
    }

    public function rechargeList(Request $request)
    {
        $limit = $request->input('limit', 20);
        // $agent = Agent::getAgent();
        // $child_agents = Agent::getAllChildAgent($agent->id);
        // $agents = $child_agents->pluck('id')->all();
        // $child_users = User::whereIn('agent_node_id', $agents)->get();
        $agent_id = Agent::getAgentId();
        $node_users = User::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $recharge_account_type_id = AccountType::where('is_recharge', AccountType::IS_RECHARGE)
        ->value('id');
        $class = AccountType::getAccountClass($recharge_account_type_id);
        $logClass = $class->logClass;
        $lists = $logClass::where('type', AccountLog::BLOCK_CHAIN_ADD[0])
            //->whereIn('user_id', $child_users->pluck('id')->all())
            ->whereIn('user_id', $node_users)
            ->where(function ($query) use ($request) {

                $uid = $request->input('uid', '');
                $email = $request->input('email', '');
                $mobile = $request->input('mobile', '');
                $belong_agent = $request->input('belong_agent', '');
                $currency_id = $request->input('currency_id', -1);

                $query->whereHas('user', function ($query) use ($uid, $mobile, $email) {
                       $email!="" && $query->where('email', $email);
                       $mobile!="" && $query->where('mobile', $mobile);
                       $uid!="" && $query->where('uid', $uid);
                })->when($belong_agent != '', function ($query) use ($belong_agent) {
                    $query->whereHas('user', function ($query) use ($belong_agent) {
                        $query->whereHas('belongAgent', function ($query) use ($belong_agent) {
                            $query->where('username', $belong_agent);
                        });
                    });
                })->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            // 设置上级代理商信息
            $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
            $item->setAttribute('account', $item->user->account ?? '');
            $item->setAttribute('currency_name', $item->currency->code ?? '');
            return $item;
        });
        $lists->setCollection($items);
        return $this->layuiPageData($lists);
    }

    //提币
    public function withdrawList(Request $request)
    {
        $limit = $request->input('limit', 20);


        // $agent = Agent::getAgent();
        // $child_agents = Agent::getAllChildAgent($agent->id);
        // $agents = $child_agents->pluck('id')->all();
        // $child_users = Users::whereIn('agent_node_id', $agents)->get();
        $agent_id = Agent::getAgentId();
        $node_users = User::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $lists =AccountDraw::whereIn('user_id', $node_users)
//            ->whereIn('user_id', $node_users)
//            ->where(function ($query) use ($request) {
//
//                $uid = $request->input('uid', '');
//                $email = $request->input('email', '');
//                $mobile = $request->input('mobile', '');
//                $belong_agent = $request->input('belong_agent', '');
//                $currency_id = $request->input('currency_id', -1);
//
//                $query->whereHas('user', function ($query) use ($uid, $mobile, $email) {
//                    $email!="" && $query->where('email', $email);
//                    $mobile!="" && $query->where('mobile', $mobile);
//                    $uid!="" && $query->where('uid', $uid);
//                })->when($belong_agent != '', function ($query) use ($belong_agent) {
//                    $query->whereHas('user', function ($query) use ($belong_agent) {
//                        $query->whereHas('belongAgent', function ($query) use ($belong_agent) {
//                            $query->where('username', $belong_agent);
//                        });
//                    });
//                })->when($currency_id > 0, function ($query) use ($currency_id) {
//                    $query->where('currency_id', $currency_id);
//                });
//            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            // 设置上级代理商信息
            if ($item->notes == '') {
                $item->notes = '用户提币';
            }
            $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
            $item->setAttribute('account', $item->user->account ?? '');
            $item->setAttribute('currency_name', $item->currency->code ?? '');
            return $item;
        });
        $lists->setCollection($items);
        return $this->layuiPageData($lists);
    }

    //用户资金
    public function wallet(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }

        return view("agent.capital.wallet", ['user_id' => $id]);
    }

    public function wallettotalList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = $request->get('user_id', null);
        if (empty($user_id)) {
            return $this->error('参数错误');
        }

        $list = Currency::orderBy('id', 'desc')->select(['id','accounts_display','code'])->paginate($limit);
        foreach ($list->items() as &$value) {

            $accounts_display=$value->accounts_display;
            if (!in_array(AccountType::CHANGE_ACCOUNT_ID,$accounts_display)){
                unset($value);
            }else{
            $value->_ru = ChangeAccountLog::where('type', AccountLog::BLOCK_CHAIN_ADD[0])
            ->where('user_id', $user_id)
            ->where('currency_id', $value->id)
            ->sum('value');


            $value->_chu =AccountDraw::where('status', 2)
            ->where('user_id', $user_id)
            ->where('currency_id', $value->id)
            ->sum('real_number');

            $value->_caution_money = LeverTransaction::where('user_id', $user_id)->whereIn('status', [0, 1,2])->where('quote_currency_id', $value->id)->sum('caution_money');
            }
            $aa= MicroAccount::where('user_id', $user_id)->where('currency_id', $value->id)->select('balance')->first();
            if($aa){
                   $value->_micro_money=$aa->balance;
            }else{
               $value->_micro_money='0.00000000';
            }
            //dd($value->_micro_money);
            //dd($aa?$aa->balance:'0.00000000');
           $bb=ChangeAccount::where('user_id', $user_id)->where('currency_id', $value->id)->select('balance')->first();
          if($bb){
                   $value->_change_money=$bb->balance;
            }else{
               $value->_change_money='0.00000000';
            }
            $cc= LeverAccount::where('user_id', $user_id)->where('currency_id', $value->id)->select('balance')->first();
            if($cc){
                   $value->_lever_money=$cc->balance;
            }else{
               $value->_lever_money='0.00000000';
            }
           //$value->_change_money=$bb?$bb->balance:'0.00000000';
        
        }
        return $this->layuiPageData($list);
    }


}
