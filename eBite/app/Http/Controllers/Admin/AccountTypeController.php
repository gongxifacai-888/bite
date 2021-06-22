<?php


namespace App\Http\Controllers\Admin;


use App\Models\Account\AccountType;

class AccountTypeController extends Controller
{
    public function index()
    {
        return view('admin.accountType.index');
    }

    public function list()
    {
        $account_types = AccountType::paginate();
        return $this->layuiPageData($account_types);
    }

    public function updateRecharge()
    {
        $id    = request('id');
        $model = AccountType::findOrFail($id);
        if ($model->is_recharge == AccountType::IS_RECHARGE) {
            return transaction(function () use ($id) {
                //除了ID为当前ID的第一条更改为允许的状态
                AccountType::whereNotNull('is_recharge')->update([
                    'is_recharge' => AccountType::NO_RECHARGE
                ]);
                AccountType::where('id', '!=', $id)->firstOrFail()
                    ->update(['is_recharge' => AccountType::IS_RECHARGE]);
                return $this->success('操作成功');
            });
        } else {
            AccountType::where('id', '!=', $id)
                ->update(['is_recharge' => AccountType::NO_RECHARGE]);
            $model->is_recharge = AccountType::IS_RECHARGE;
            $model->save();
        }
        return $this->success('操作成功');
    }

    public function updateDisplay()
    {
        $id    = request('id');
        $model = AccountType::findOrFail($id);
        if ($model->is_display == AccountType::STATUS_ON) {
            $model->is_display = AccountType::STATUS_OFF;
        } else {
            $model->is_display = AccountType::STATUS_ON;
        }
        $model->save();
        return $this->success('操作成功');
    }
}
