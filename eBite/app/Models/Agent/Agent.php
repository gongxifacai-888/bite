<?php


namespace App\Models\Agent;

use App\Models\Setting\Setting;
use App\Models\User\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Models\Model;

class Agent extends Model
{

    public $timestamps = false;
    protected $appends = [
        'parent_agent_name',
        'agent_name',
        'self_info',
        'son_level',
        'max_pro_loss',
        'max_pro_ser',
        'mobile',
        'email'
    ];


    public function getPasswordAttribute()
    {
        return '**********';
    }

    /**
     * @param $mobile
     *
     * @return Agent|string
     */
    public static function getAgentById($id = 0)
    {
        if ($id == 0) return "";
        return self::where("id", $id)->first();
    }

    public static function getAgentId()
    {
        if (session()->has('access_token')) {
            $access_token = session()->get('access_token');
            return session()->get($access_token);
        } else {
            return session()->get('agent_id');
        }
    }

    /**
     * 获取该用户的代理商信息
     *
     * @return string
     */
    public static function getAgent()
    {
        return self::getAgentById(self::getAgentId());
    }

    public static function getUserByUsername($username)
    {
        if (empty($username)) return "";
        return self::where("username", $username)->first();
    }


    /**
     * @param $obj
     */
    public static function delSession($request)
    {

        if ($request->session()->has('agent_id')) {

            session()->put('agent_id', '');
            session()->put('agent_username', '');

            return true;
        } elseif (session()->has('access_token')) {
            $access_token = session()->get('access_token');
            session()->put($access_token, '');
            session()->put('access_token', '');
            return true;
        } else {
            return false;
        }
    }


    public function user()
    {

        return $this->belongsTo(User::class, 'user_id', 'id');

    }


    public static function getAllChildAgent($agent_id, $refresh = false)
    {
        if ($refresh) {
            $child_agents = self::getRealChildAgent($agent_id);
        } else {
            if (Cache::has("child_agents:{$agent_id}")) {
                $child_agents = Cache::get("child_agents:{$agent_id}");
            } else {
                $child_agents = self::getRealChildAgent($agent_id);
            }
        }
        return $child_agents;
    }

    private static function getRealChildAgent($agent_id)
    {
        $child_agents = self::whereRaw('find_in_set(?, agent_path)', [$agent_id])->get();
        Cache::put("child_agents:{$agent_id}", $child_agents, Carbon::now()->addMinutes(5));
        return $child_agents;
    }

    /**
     * 注册的时候，获取上级用户的上级代理商
     */
    public static function getAgentIdByParentId($parent = 0)
    {
        if ($parent == 0) {
            //无上级是，获取admin的id
            $p = self::where('is_admin', 1)->where('level', 0)->first();
            return $p->id ?? 0;
        } else {

            $_p_info = User::find($parent);
            if (!empty($_p_info)) {
                //如果该用户是代理商，返回该用户的代理商id
                if ($_p_info->agent_id > 0) {
                    return $_p_info->agent_id;
                } else if ($_p_info->agent_id == 0 && $_p_info->agent_node_id > 0) {
                    //如果该用户不是代理商，但是用户属于某个代理商，返回所属代理商id
                    return $_p_info->agent_node_id;
                } else {
                    //其他情况，
                    $p = self::where('is_admin', 1)->where('level', 0)->first();
                    return $p->id;
                }
            } else {
                //无上级是，获取admin的id
                $p = self::where('is_admin', 1)->where('level', 0)->first();
                return $p->id;
            }
        }
    }

    //获取用户的代理商关系
    public static function agentPath($parent = 0)
    {


        if ($parent == 0) {
            //无上级是，获取admin的id
            $p = self::where('is_admin', 1)->where('level', 0)->first();
            return $p->id ?? 0;
        } else {
            $_p_info = User::find($parent);
            if (!empty($_p_info)) {
                if ($_p_info->agent_id > 0) {
                    //
                    $agent = self::find($_p_info->agent_id);

                    return $agent ? $agent->agent_path : $_p_info->agent_path;
                } else {

                    return $_p_info->agent_path;
                }
            } else {
                //无上级是，获取admin的id
                $p = self::where('is_admin', 1)->where('level', 0)->first();
                return $p->id;
            }
        }
    }

    public function getParentAgentNameAttribute()
    {
        $value = $this->attributes['parent_agent_id'];
        if ($value > 0) {
            $p = self::getAgentById($value);
            return $p ? $p->username : '';
        } else {
            return __('model.无');
        }
    }

    public function getAgentNameAttribute()
    {
        $value = $this->attributes['level'];
        if ($value > 0) {
            $p = $value . __('model.级代理商');
            return $p;
        } else if ($value == 0) {
            return __('model.超级');
        } else {
            return __('model.无');
        }
    }

    public function getRegTimeAttribute()
    {
        $value = $this->attributes['reg_time'];
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        } else {
            return __('model.无');
        }
    }

    public function getSelfInfoAttribute()
    {
        $value = $this->attributes['level'];
        $agent_max_level = Setting::getValueByKey('agent_max_level', 4);
        if ($value > 0 && $value <= $agent_max_level) {
            $_level = $value . __('model.级代理商');

        } else {
            $_level = __('model.超管');
        }

        return $_level;
    }

    public function getSonLevelAttribute()
    {
        $level = $this->attributes['level'];
        $is_admin = $this->attributes['is_admin'];
        if ($level >= 0 && $is_admin >= 0) {

            if ($level == 0 && $is_admin == 1) {
                $son_level = 1;
            } else if ($is_admin == 0) {
                $son_level = $level + 1;
            }
            return $son_level;
        } else {
            return __('model.无');
        }
    }

    public function getMaxProLossAttribute()
    {
        $value = $this->attributes['pro_loss'];
        $p_id = $this->attributes['parent_agent_id'];
        $p_info = self::getAgentById($p_id);
        if ($p_info != null) {
            return $p_info['pro_loss'];
        } else {
            return $value;
        }
    }

    public function getMaxProSerAttribute()
    {
        $value = $this->attributes['pro_ser'];
        $p_id = $this->attributes['parent_agent_id'];
        $p_info = self::getAgentById($p_id);
        if ($p_info != null) {
            return $p_info['pro_ser'];
        } else {
            return $value;
        }
    }


    public static function changeAgentMoney($agent_id, $type, $change, $relate_id, $son_user_id, $quote_currency_id)
    {
        //记录余额专用流水明细
        $moneyLog = new AgentMoneyLog();
        $moneyLog->agent_id = $agent_id;
        $moneyLog->type = $type;
        $moneyLog->relate_id = $relate_id;
        $moneyLog->change = $change;
        $moneyLog->user_id = $son_user_id;
        $moneyLog->quote_currency_id = $quote_currency_id;

        $moneyLog->save();
    }

    public function getMobileAttribute()
    {

        $is_admin = $this->attributes['is_admin'];
        if ($is_admin == 1) {
            return '';
        } else {
            return self::hasOne(User::class, 'id', 'user_id')->value('mobile');
        }
    }

    public function getEmailAttribute()
    {

        $is_admin = $this->attributes['is_admin'];
        if ($is_admin == 1) {
            return '';
        } else {
            return self::hasOne(User::class, 'id', 'user_id')->value('email');
        }
    }


}
