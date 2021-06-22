<?php


namespace App\Http\Controllers\Admin;

use App\Models\Account\AccountType;
use App\Models\Account\ChangeAccountLog;
use App\Models\Account\FinancialAccountLog;
use App\Models\Account\IsoAccountLog;
use App\Models\Account\LegalAccountLog;
use App\Models\Account\LeverAccountLog;
use App\Models\Account\AccountLog;
use App\Models\Account\MicroAccountLog;
use App\Models\Currency\Currency;
use App\Models\User\User;

class AccountLogController extends Controller
{
    public function initAccountLogTypesAndCurrencies()
    {
        $types = AccountLog::getAccountLogType();
        $currencies = Currency::get();
        view()->share('types', $types);
        view()->share('currencies', $currencies);
    }

    public function legal()
    {
        $this->initAccountLogTypesAndCurrencies();
        $type = request('type', 0);
        return view('admin.accountLog.legal', [
            'type' => $type
        ]);
    }

    public function change()
    {
        $this->initAccountLogTypesAndCurrencies();
        $type = request('type', 0);
        return view('admin.accountLog.change', [
            'type' => $type
        ]);
    }

    public function lever()
    {
        $this->initAccountLogTypesAndCurrencies();
        $type = request('type', 0);
        return view('admin.accountLog.lever', [
            'type' => $type
        ]);
    }

    public function iso()
    {
        $this->initAccountLogTypesAndCurrencies();
        $type = request('type', 0);
        return view('admin.accountLog.iso', [
            'type' => $type
        ]);
    }

    public function micro()
    {
        $this->initAccountLogTypesAndCurrencies();
        $type = request('type', 0);
        return view('admin.accountLog.micro', [
            'type' => $type
        ]);
    }
    public function financial()
    {
        $this->initAccountLogTypesAndCurrencies();
        $type = request('type', 0);
        return view('admin.accountLog.financial', [
            'type' => $type
        ]);
    }
    public function recharge()
    {
        $this->initAccountLogTypesAndCurrencies();
        return view('admin.accountLog.recharge');
    }

    public function leverList()
    {
        $type = request('type', 0);
        $currency_id = request('currency_id', 0);
        $limit = request('limit', '');
        $uid = request('uid', '');
        $email = request('email', '');
        $mobile = request('mobile', '');
        $where = [];
        if ($type) {
            $where[] = ['type', $type];
        }
        if ($currency_id) {
            $where[] = ['currency_id', $currency_id];
        }
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                return $this->error('暂无数据');
            }
            $where[] = ['user_id', $user->id];
        }
        $list = LeverAccountLog::with(['user', 'currency'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->where($where)->orderBy('id', 'DESC')->paginate($limit);
        $sum = LeverAccountLog::where($where)->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->sum('value');

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }

    public function changeList()
    {
        $type = request('type', 0);
        $currency_id = request('currency_id', 0);
        $limit = request('limit', '');
        $uid = request('uid', '');
        $email = request('email', '');
        $mobile = request('mobile', '');
        $where = [];
        if ($type) {
            $where[] = ['type', $type];
        }
        if ($currency_id) {
            $where[] = ['currency_id', $currency_id];
        }
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                return $this->error('暂无数据');
            }
            $where[] = ['user_id', $user->id];
        }
        $list = ChangeAccountLog::with(['user', 'currency'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->where($where)->orderBy('id', 'DESC')->paginate($limit);
        $sum = ChangeAccountLog::where($where)->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->sum('value');

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }

    public function legalList()
    {
        $type = request('type', 0);
        $currency_id = request('currency_id', 0);
        $limit = request('limit', '');
        $uid = request('uid', '');

        $email = request('email', '');
        $mobile = request('mobile', '');
        $where = [];
        if ($type) {
            $where[] = ['type', $type];
        }
        if ($currency_id) {
            $where[] = ['currency_id', $currency_id];
        }
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                return $this->error('暂无数据');
            }
            $where[] = ['user_id', $user->id];
        }

        $list = LegalAccountLog::with(['user', 'currency'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->where($where)->orderBy('id', 'DESC')->paginate($limit);
        $sum = LegalAccountLog::where($where)->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->selectRaw('sum(abs(`value`)) as total')->first()->total;

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }

    public function microList()
    {
        $type = request('type', 0);
        $currency_id = request('currency_id', 0);
        $limit = request('limit', '');
        $uid = request('uid', '');
        $email = request('email', '');
        $mobile = request('mobile', '');

        $where = [];
        if ($type) {
            $where[] = ['type', $type];
        }
        if ($currency_id) {
            $where[] = ['currency_id', $currency_id];
        }
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                return $this->error('暂无数据');
            }
            $where[] = ['user_id', $user->id];
        }
        $list = MicroAccountLog::with(['user', 'currency'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->where($where)->orderBy('id', 'DESC')->paginate($limit);
        $sum = MicroAccountLog::where($where)->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->selectRaw('sum(abs(`value`)) as total')->first()->total;

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }


    public function isoList()
    {
        $type = request('type', 0);
        $currency_id = request('currency_id', 0);
        $limit = request('limit', '');
        $uid = request('uid', '');
        $email = request('email', '');
        $mobile = request('mobile', '');
        $where = [];
        if ($type) {
            $where[] = ['type', $type];
        }
        if ($currency_id) {
            $where[] = ['currency_id', $currency_id];
        }
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                return $this->error('暂无数据');
            }
            $where[] = ['user_id', $user->id];
        }
        $list = IsoAccountLog::with(['user', 'currency'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->where($where)->orderBy('id', 'DESC')->paginate($limit);
        $sum = IsoAccountLog::where($where)->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->selectRaw('sum(abs(`value`)) as total')->first()->total;

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }
    public function financialList()
    {
        $type = request('type', 0);
        $currency_id = request('currency_id', 0);
        $limit = request('limit', '');
        $uid = request('uid', '');
        $email = request('email', '');
        $mobile = request('mobile', '');

        $where = [];
        if ($type) {
            $where[] = ['type', $type];
        }
        if ($currency_id) {
            $where[] = ['currency_id', $currency_id];
        }
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                return $this->error('暂无数据');
            }
            $where[] = ['user_id', $user->id];
        }
        $list = FinancialAccountLog::with(['user', 'currency'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->where($where)->orderBy('id', 'DESC')->paginate($limit);
        $sum = FinancialAccountLog::where($where)->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->sum('value');

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }

    public function rechargeList()
    {
        $currency_id = request('currency_id', 0);
        $limit = request('limit', '');
        $uid = request('uid', '');
        $email = request('email', '');
        $mobile = request('mobile', '');

        $where = [];
        if ($currency_id) {
            $where[] = ['currency_id', $currency_id];
        }
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                return $this->error('暂无数据');
            }
            $where[] = ['user_id', $user->id];
        }
        $where[] = ['type', AccountLog::BLOCK_CHAIN_ADD[0]];

        $recharge_account_type_id = AccountType::where('is_recharge', AccountType::IS_RECHARGE)
            ->value('id');
        $class = AccountType::getAccountClass($recharge_account_type_id);
        $logClass = $class->logClass;

        $list = $logClass::with(['user', 'currency'])->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->where($where)->orderBy('id', 'DESC')->paginate($limit);

        $sum = MicroAccountLog::where($where)->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->sum('value');

        return $this->layuiPageData($list, '请求成功', [
            'sum' => $sum
        ]);
    }
}
