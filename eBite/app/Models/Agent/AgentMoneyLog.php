<?php

namespace App\Models\Agent;


use App\Models\Account\AccountLog;
use App\Models\Account\ChangeAccount;
use App\Models\Currency\Currency;
use App\Models\Lever\LeverTransaction;
use App\Models\Micro\MicroOrder;
use App\Models\Model;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class AgentMoneyLog extends Model
{
    //合约头寸
    const LEVER_LOSS = 1;
    //合约手续费
    const LEVER_FEE = 2;
    //秒合约头寸
    const MICRO_LOSS = 3;
    //秒合约手续费
    const MICRO_FEE = 4;

    const NO_DRAW = 0;
    const DRAW = 1;

    protected $table = 'agent_money_logs';
    public $timestamps = false;
    protected $appends = [
        'agent_level',
        'jie_agent_name',
        'jie_agent_level',
        'legal_name',
        'type_name',
        'type_name',
    ];

    public function getTypeNameAttribute()
    {
        $type = $this->getAttribute('type');
        $name[self::LEVER_LOSS] = '合约头寸';
        $name[self::LEVER_FEE] = '合约手续费';
        $name[self::MICRO_LOSS] = '极速合约头寸';
        $name[self::MICRO_FEE] = '极速合约手续费';
        return $name[$type] ?? '未知';
    }

    public function getJieAgentNameAttribute()
    {
        $agent = $this->agent()->getResults();

        return $agent ? $agent->username : '';
    }

    public function getJieAgentLevelAttribute()
    {
        $agent = $this->agent()->getResults();
        return $agent->is_admin == 1 ? __('model.超管') : $agent->level . __('model.级代理商');
    }

    public function getLegalNameAttribute()
    {
        $value = $this->attributes['quote_currency_id'];
        $legal = Currency::where('id', $value)->first();

        return $legal ? $legal->code : '';
    }


    public function getAgentLevelAttribute()
    {

        $user = $this->user()->getResults();
        if ($user->agent_id == 0) {
            return __('model.普通用户');
        } else {

            $agent = Agent::find($user->agent_id);

            if ($agent && $agent->level == 0) {
                $agent_name = __('model.超管');
            } else if ($agent && $agent->level > 0) {
                $agent_name = $agent->level . __("model.级代理商");
            } else {
                $agent_name = __('model.普通用户');
            }
            return $agent_name;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function leverOrder()
    {
        return $this->belongsTo(LeverTransaction::class, 'relate_id', 'id');
    }

    public function microOrder()
    {
        return $this->belongsTo(MicroOrder::class, 'relate_id', 'id');
    }

    /**提现
     *
     * @throws \Exception
     */
    public function draw()
    {
        $agent = $this->agent;

        if ($agent->is_admin) {
            throw new \Exception('超管无法提现');
        }

        $wallet = ChangeAccount::where('user_id', $agent->user_id)
            ->where('currency_id', $this->quote_currency_id)->lockForUpdate()->first();
        if (empty($wallet)) {
            throw new \Exception('用户钱包不存在');
        }
        if ($this->type == AgentMoneyLog::LEVER_LOSS) {
            $account_info = AccountLog::LEVER_LOSS_AGENT_CHANGE;
        } elseif ($this->type = AgentMoneyLog::LEVER_FEE) {
            $account_info = AccountLog::LEVER_FEE_AGENT_CHANGE;
        } elseif ($this->type = AgentMoneyLog::MICRO_FEE) {
            $account_info = AccountLog::MICRO_FEE_AGENT_CHANGE;
        } elseif ($this->type = AgentMoneyLog::MICRO_LOSS) {
            $account_info = AccountLog::MICRO_LOSS_AGENT_CHANGE;
        }
        $wallet->changeBalance($account_info, $this->change);
        $this->status = self::DRAW;
        $this->save();
    }
}
