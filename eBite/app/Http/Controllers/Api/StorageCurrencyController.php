<?php

namespace App\Http\Controllers\Api;


use App\Models\Account\Account;
use App\Models\Account\AccountLog;
use App\Models\Account\AccountType;
use App\Models\Patch\StorageCurrency;
use App\Models\Patch\StorageCurrencyHistory;
use App\Models\Setting\Setting;
use App\Models\User\User;

class StorageCurrencyController extends Controller
{
    //获取信息
    public function lockList()
    {
        $limit = request('limit', 20);
        $list = StorageCurrency::with(['currency'])->where('status', StorageCurrency::STATUS_ON)->orderBy('created_at', 'DESC')->paginate($limit);
        return $this->success(__('api.请求成功'), $list);
    }

    public function currentSetting()
    {
        $data = [
            'display_rate' => Setting::getValueByKey('current_display_rate'),
            'rate'         => Setting::getValueByKey('current_rate'),
        ];
        return $this->success(__('api.请求成功'), $data);
    }

    //
    public function submitLock()
    {
        return transaction(function () {
            $id = request('id');
            $user = User::getUser();
            $user_id = $user->id;
            $lock = StorageCurrency::findOrFail($id);
            if ($lock->status != StorageCurrency::STATUS_ON) {
                return $this->error(__('api.暂时无法买入'));
            }
            $account = Account::getAccountByType(AccountType::CHANGE_ACCOUNT_ID, $lock->currency_id, $user->id);
            $account->changeBalance(AccountLog::FINANCIAL_DEC, -$lock->limit_number);
            StorageCurrencyHistory::create([
                'user_id'             => $user_id,
                'currency_id'         => $lock->currency_id,
                'storage_currency_id' => $lock->id,
                'surplus_days'        => $lock->limit_days,
            ]);
            //给推荐人增加存币宝账户余额
            if ($user->parent_id > 0) {
                $parent_inc_number = bc($lock->limit_number, '*', $lock->parent_rate);
                $parent_account = Account::getAccountByType(AccountType::FINANCIAL_ACCOUNT_ID, $lock->currency_id, $user->parent_id);
                $parent_account->changeBalance(AccountLog::FINANCIAL_INVITE_INC, $parent_inc_number, ['from' => $user->account]);
            }
            return $this->success(__('api.操作成功'));
        });
    }

    public function lockHistory()
    {
        $limit = request('limit', 20);
        $user_id = User::getUserId();
        $list = StorageCurrencyHistory::with(['storageCurrency'])
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit);
        return $this->success(__('api.请求成功'), $list);
    }
}
