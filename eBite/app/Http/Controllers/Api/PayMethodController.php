<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\System\Payway;
use App\Models\User\User;
use App\Models\User\UserPayway;
use Illuminate\Http\Request;

class PayMethodController extends Controller
{

    public function getMethodType()
    {
        $types = Payway::get();
        return $this->success(__('api.请求成功'), $types);
    }

    /**
     * 获取用户所有支付方法
     * @return \Illuminate\Http\JsonResponse
     */
    public function methods()
    {
        $user_id = User::getUserId();
        $payMethods = UserPayway::with(['payway'])->where('user_id', $user_id)->get();
        return $this->success(__('api.请求成功'), $payMethods);
    }

    public function saveMethod(Request $request)
    {
        $types = Payway::pluck('id')->toArray();
        $type = $request->post('type');
        $raw_data = $request->post('raw_data');
        $user_id = User::getUserId();
        if (!in_array($type, $types)) {
            return $this->error(__('api.类型错误'));
        }
        UserPayway::updateOrCreate(['user_id' => $user_id, 'payway_id' => $type], ['raw_data' => $raw_data]);
        return $this->success(__('api.操作成功'));
    }

    public function getMethodByType(Request $request)
    {
        $type = $request->get('type');
        $user_id = User::getUserId();
        $method = UserPayway::where('user_id', $user_id)->where('payway_id', $type)->first();

        return $this->success(__('api.请求成功'), $method);
    }
}
