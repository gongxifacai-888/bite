<?php


namespace App\Http\Controllers\Admin;

use App\Models\Account\AccountLog;
use App\Models\Account\AccountType;
use App\Exceptions\ThrowException;
use App\Models\User\User;


class AccountController extends Controller
{
    public function index()
    {
        $uid = request('uid', '');

        $account_types = AccountType::where('is_display', AccountType::STATUS_ON)->get();
        return view('admin.account.index', [
            'uid' => $uid,
            'account_types' => $account_types,
        ]);
    }

    public function list()
    {
        $limit = request('limit', 10);
        $account_type_id = request('account_type_id', 0);
        $uid = request('uid', 0);
        $email = request('email', '');
        $mobile = request('mobile', '');

        $accounts = AccountType::getAccountClass($account_type_id)
            ->with('user')->whereHas('currency')
            ->when($uid, function ($query, $uid) {
                $user_id = User::where('uid', $uid)->value('id');
                $query->where('user_id', $user_id);
            })->when($email, function ($query, $email) {
                $user_id = User::where('email', $email)->value('id');
                $query->where('user_id', $user_id);
            })->when($mobile, function ($query, $mobile) {
                $user_id = User::where('mobile', $mobile)->value('id');
                $query->where('user_id', $user_id);
            })->orderByDesc('balance')->paginate($limit);
 //dd($accounts);
        $accounts->getCollection()->each(function ($account) {
            $account->addHidden('currency', 'user_id', 'currency_id');
            $account->append('convert_usd', 'convert_cny', 'currency_code', 'sum_balance', 'uid');
        });

        return $this->layuiPageData($accounts);
    }

    public function edit()
    {
        $account_id = request('account_id', 0);
        $account_type_id = request('account_type_id', 0);

        $account = AccountType::getAccountClass($account_type_id)->find($account_id);

        return view('admin.account.edit', [
            'account' => $account
        ]);
    }

    /**
     * @return \Illuminate\Support\Facades\Response
     * @throws \Throwable
     */
    public function update()
    {
        return transaction(function () {
            $account_id = request('account_id', 0);
            $account_type_id = request('account_type_id', 0);
            $number = request('number', 0);
            $memo = request('memo', '');
            $method = request('method', '');

            if (!$memo) {
                //                throw new ThrowException('请输入备注');
                return $this->error('请输入备注');

            }

            $account = AccountType::getAccountClass($account_type_id)->lockForUpdate()->findOrFail($account_id);

            $account->$method(AccountLog::ADMIN_CHANGE, $number, [
                'memo' => $memo
            ]);

            return $this->success('更改成功');
        });
    }
}
