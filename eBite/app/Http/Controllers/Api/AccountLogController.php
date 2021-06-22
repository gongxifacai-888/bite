<?php


namespace App\Http\Controllers\Api;

use App\Models\Account\{AccountLog,
    ChangeAccountLog,
    FinancialAccountLog,
    LegalAccountLog,
    LeverAccountLog,
    MicroAccountLog};
use App\Models\User\User;

class AccountLogController extends Controller
{
    public function change()
    {
        $currency_id = request('currency_id');
        $limit       = request('limit', 15);
        $logs        = ChangeAccountLog::with(['currency'])->when($currency_id, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->where('user_id', User::getUserId())
            ->where('is_lock', AccountLog::NO_LOCK)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success(__('api.请求成功'), $logs);
    }


    public function lever()
    {
        $currency_id = request('currency_id');
        $limit       = request('limit', 15);
        $logs        = LeverAccountLog::with(['currency'])->when($currency_id, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->where('user_id', User::getUserId())
            ->where('is_lock', AccountLog::NO_LOCK)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success(__('api.请求成功'), $logs);
    }

    public function legal()
    {
        $currency_id = request('currency_id');
        $limit       = request('limit', 15);
        $logs        = LegalAccountLog::with(['currency'])->when($currency_id, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->where('user_id', User::getUserId())
            ->where('is_lock', AccountLog::NO_LOCK)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success(__('api.请求成功'), $logs);
    }

    public function micro()
    {
        $currency_id = request('currency_id');
        $limit       = request('limit', 15);
        $logs        = MicroAccountLog::with(['currency'])->when($currency_id, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->where('user_id', User::getUserId())
            ->where('is_lock', AccountLog::NO_LOCK)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success(__('api.请求成功'), $logs);
    }

    public function financial()
    {
        $currency_id = request('currency_id');
        $limit       = request('limit', 15);
        $logs        = FinancialAccountLog::with(['currency'])->when($currency_id, function ($query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })->where('user_id', User::getUserId())
            ->where('is_lock', AccountLog::NO_LOCK)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success(__('api.请求成功'), $logs);
    }
}
