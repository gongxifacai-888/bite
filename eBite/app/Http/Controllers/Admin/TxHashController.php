<?php


namespace App\Http\Controllers\Admin;

use App\Models\Chain\TxHash;
use App\Models\User\User;
use Illuminate\Http\Request;

class TxHashController extends Controller
{
    public function index(Request $request)
    {
        $list = TxHash::getHashTypes();
        $type = $request->get('type', -1);
        return view('admin.txHash.index', [
            'type' => $type,
            'type_list' => $list
        ]);
    }

    public function list(Request $request)
    {
        $type = $request->get('type', -1);
        $uid = request('uid');
        $limit = $request->get('limit', 20);
        $status = $request->get('status', -1);
        $email = request('email', '');
        $mobile = request('mobile', '');

        $list = TxHash::with(['user', 'currencyProtocol' => function ($query) {
            $query->with('chainProtocol', 'currency');
        }])->when($type != -1, function ($query) use ($type) {
            $query->where('type', $type);
        })->when($status != -1, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->when($uid, function ($query, $uid) {
            $query->whereHas('user', function ($query) use ($uid) {
                $query->where('uid', $uid);
            });
        })->orderBy('id', 'desc')->paginate($limit);

        return $this->layuiPageData($list);
    }
}
