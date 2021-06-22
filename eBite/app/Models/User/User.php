<?php


namespace App\Models\User;

use App\Logic\GoChain;
use App\Models\Account\AccountType;
use App\Models\Agent\Agent;
use App\Models\Chain\ChainWallet;
use App\Models\Feedback\Feedback;
use App\Models\Model;

use App\BlockChain\BlockChain;
use App\Models\Otc\Seller;
use App\Models\System\Area;
use Illuminate\Support\Str;
use App\Logic\User as UserLogic;
use App\Exceptions\ThrowException;

class User extends Model
{
    const EMAIL  = 'email';
    const MOBILE = 'mobile';

    //是否锁定
    const LOCK   = 2;
    const UNLOCK = 1;

    protected $hidden = [
        'password',
        'pay_password',
        'last_login_ip',
        'risk',
    ];
    protected static $riskList = [
        \App\Models\Micro\MicroOrder::RESULT_LOSS => '亏损',
        \App\Models\Micro\MicroOrder::RESULT_BALANCE => '无',
        \App\Models\Micro\MicroOrder::RESULT_PROFIT => '盈利',
    ];
    public function seller()
    {
        return $this->hasOne(Seller::class, 'user_id', 'id')->withDefault([]);
    }

    public function userPayways()
    {
        return $this->hasMany(UserPayway::class, 'user_id', 'id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'user_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = self::encryptPassword($password);
    }

    public function setPayPasswordAttribute($pay_password)
    {
        $this->attributes['pay_password'] = self::encryptPassword($pay_password);
    }

    public function getMobileAttribute()
    {
        return $this->attributes['mobile'] ?? __('api.无');
    }

    public function getEmailAttribute()
    {
        return $this->attributes['email'] ?? __('api.无');
    }

    public function getStatusNameAttribute()
    {
        $status             = $this->getAttribute('status');
        $name[self::UNLOCK] = '正常';
        $name[self::LOCK]   = '封停';
        return $name[$status] ?? '未知';
    }

    public function getAccountAttribute()
    {
        return $this->getAttribute('mobile') ?: $this->getAttribute('email');
    }
    public function getRiskNameAttribute()
    {
        $risk = $this->attributes['risk'] ?? 0;
        return self::$riskList[$risk];
    }
    /**
     * 加密密码
     *
     * @param $password
     *
     * @return string
     */
    public static function encryptPassword($password)
    {
        return md5('LBX_NB' . $password);
    }

    /**
     * 生成邀请码
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateInviteCode($length = 6)
    {
        $code = Str::random($length);
        $code = Str::upper($code);
        if (self::where("invite_code", $code)->first()) {
            //如果生成的邀请码存在，继续生成，直到不存在
            $code = self::generateInviteCode();
        }
        return $code;
    }

    /**
     * 生成邀请码
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateUid()
    {
        $uid = mt_rand(1000000000, 9999999999);
        if (self::where("uid", $uid)->first()) {
            //如果生成的邀请码存在，继续生成，直到不存在
            $uid = self::generateUid();
        }
        return $uid;
    }

    /**
     * 登陆
     *
     * @param $password
     *
     * @return string
     * @throws \Exception
     */
    public function login($password)
    {
        if (!$this->exists) {
            throw new ThrowException(__('model.用户不存在'));
        }
        if (!$this->checkPassword($password)) {
            throw new ThrowException(__('model.密码输入有误,请重新输入'));
        }
        //检测用户是否被锁定
        if ($this->status == self::LOCK) {
            throw new ThrowException(__('model.网络异常'));
        }

        $this->last_login_ip = request()->ip();
        $this->last_login_at = now();
        $this->save();
        return Token::setToken($this->id);
    }

    /**检查密码是否正确
     *
     */
    public function checkPassword($password)
    {
        if ($this->password != $this->encryptPassword($password)) {
            return false;
        }
        return true;
    }

    /**
     * 用户注册
     *
     * @param $account              string    账号
     * @param $password             string    密码
     * @param $register_type        string   注册类型[邮箱或者是手机]
     * @param $invite_code          string   邀请码
     * @param $area_id              string   地区id
     *
     * @return User
     * @throws \Exception
     */
    public static function register($account, $password, $register_type, $invite_code, $area_id)
    {
        $parent_id = User::where('invite_code', $invite_code)->value('id') ?? 0;

        if (!$parent_id && $invite_code && $invite_code != 0) {
            throw new ThrowException(__('api.推荐码错误'));
        }

        $user = self::create([
            'password'     => $password,
            'parent_id'    => $parent_id,
            'area_id'      => $area_id,
            'invite_code'  => self::generateInviteCode(),
            'uid'          => self::generateUid(),
            $register_type => $account,
        ]);

        //创建上级路径
        $user->parents_path = UserLogic::getRealParentsPath($user);
        //寻找上级代理商id
        $user->agent_node_id = Agent::getAgentIdByParentId($parent_id);
        //代理商路径
        $user->agent_path = Agent::agentPath($parent_id);
        $user->save();
        //创建中心化账户
        $info=AccountType::generateUserAllAccount($user);
      
        //创建去中心化钱包
        //BlockChain::generateAllOnlineWallet($user);

        //$user->syncUserInfo();

        return $user;
    }

    public function syncUserInfo()
    {
        if (!$this->exists) {
            throw new ThrowException(__('model.未保存的模型'));
        }
        $result = GoChain::syncUserInfo($this);

        if (!isset($result['code']) || $result['code'] != 0) {
            throw new ThrowException(__('model.同步用户信息失败'));
        }

        return $this;
    }


    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id')->withDefault([
            'uid' => __('model.无')
        ]);
    }

    public function chainWallets()
    {
        return $this->hasMany(ChainWallet::class);
    }

    public static function getUserId()
    {
        $token   = Token::getToken();
        $user_id = Token::getUserIdByToken($token);
        return $user_id;

    }


    public static function getUser()
    {
        return self::find(self::getUserId());
    }

    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->first();
    }

    /**
     * 检测用户是否是商家
     *
     * @param boolean $valid 过滤商家状态是否必须有效
     *
     * @return boolean
     */
    public function isSeller($valid = true)
    {
        return Seller::isSeller($this, $valid);
    }

    public function belongAgent()
    {
        return $this->belongsTo(Agent::class, 'agent_node_id', 'id');
    }

    public function getParentAgentNameAttribute()
    {
        $value = $this->attributes['agent_node_id'] ?? 0;
        if ($value) {
            $p = Agent::where('id', $value)->first();
            return isset($p->username) ? $p->username : '-/-';
        }
        return '-/-';
    }

    public function getIsSellerAttribute()
    {
        return $this->isSeller();
    }

    public function getMyAgentLevelAttribute()
    {
        $value = $this->attributes['agent_id'] ?? 0;
        if ($value == 0) {
            return __('model.普通用户');
        } else {
            $m = Agent::find($value);

            if (empty($m)) {
                $name = '';
            } else {
                if ($m->level == 0) {
                    $name = __('model.超管');
                } else if ($m->level > 0) {
                    $name = $m->level . __('model.级代理商');
                }
            }

            return $name;
        }
    }
}
