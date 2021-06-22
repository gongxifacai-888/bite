<?php


namespace App\Http\Controllers\Admin;

use App\Models\Currency\Currency;
use App\Models\Account\TransferredLog;
use App\Models\User\User;
use Illuminate\Http\Request;

class TransferredLogController extends Controller
{
    public function index()
    {
        $currency = Currency::all();
        return view('admin.transferredLog.index', ['currencies' => $currency]);
    }

    public function list(Request $request)
    {
        $currency_id = $request->get('currency_id', '');
        $uid = $request->get('uid', '');
        $mobile = $request->get('mobile', '');
        $email = $request->get('email', '');
        $limit = $request->get('limit', 20);
        $list = TransferredLog::with(['user', 'currency'])
        ->whereHas('user', function ($query) use ($uid, $mobile, $email) {
            if (!empty($uid)){
                $query->where('uid', $uid);

            }
            if (!empty($mobile)){
                $query->where('uid', $mobile);

            }
            if (!empty($email)){
                $query->where('uid', $email);

            }
        })->when($currency_id, function ($query, $currency_id) {
                $query->where('currency_id', $currency_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
        return $this->layuiPageData($list);
    }
}
