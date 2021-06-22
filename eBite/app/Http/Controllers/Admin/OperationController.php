<?php


namespace App\Http\Controllers\Admin;

use App\Models\Admin\Admin;
use App\Models\Admin\OperationLog;
use Illuminate\Http\Request;

class OperationController extends Controller
{
    public function index()
    {
        return view('admin.operation.index');
    }

    public function list(Request $request)
    {
        $method = $request->get('method', '');
        $user_name = $request->get('username', '');
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $ip = $request->get('ip', '');
        $path = $request->get('path', '');
        $limit = $request->get('limit', 20);
        $list = OperationLog::with(['admin'])->when($user_name, function ($query, $user_name) {
            $query->whereHas('admin', function ($query) use ($user_name) {
                $query->where('username', $user_name);
            });
        })->when($method, function ($query, $method) {
            $query->where('method', $method);
        })->when($ip, function ($query, $ip) {
            $query->where('ip', $ip);
        })->when($path, function ($query, $path) {
            $query->where('request_path', $path);
        })->when($start_time, function ($query, $start_time) {
            $query->where('created_at', '>=', $start_time);
        })->when($end_time, function ($query, $end_time) {
            $query->where('created_at', '<=', $end_time);
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($list);
    }
}
