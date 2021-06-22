<?php

namespace App\Http\Controllers;

use App\Logic\GoChain;
use App\Models\Chain\ChainProtocol;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Models\System\Area;
use App\Models\User\User;
use App\Models\User\UserDrawaddress;
use App\Models\User\UserReal;
use App\Notifications\VerificationCode\BaseCode;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class WalletController extends Controller
{

    /**
     * 获得提现地址列表
     *
     * @param Request $request [description]
     */
    public function GetDrawAddress(Request $request)
    {
        $user_id      = request('user_id', '');
        $coin      = request('coin_name', '');
        $user = User::find($user_id);

        $user_drawaddress = UserDrawaddress::where('user_id', $user->id)->where("coin", $coin)->first();

        if ($user_drawaddress) {
            $data = [
                "code" => 0,
                "data" => [
                    'address' => $user_drawaddress->address
                ]
            ];
        } else {
            $data = [
                "code" => -1,
                "data" => [],
            ];
        }
        return json_encode($data);
    }

    /**
     * 绑定地址
     *
     * @param Request $request [description]
     */
    public function BindDrawAddress(Request $request)
    {
        $request->validate([
            'data' => 'required|json',
        ]);

        $data = json_decode($request->data, true);

        // $res = GoChain::bindWithdrawAddress(User::getUser(), $data);

        $ud = UserDrawaddress::updateOrCreate(
            ['user_id' => $data['user_id'], "coin" => $data['coin_name']],
            [
                'address' => $data['address'],
                'tokenaddress' => $data['contract_address']
            ]
        );

        $data = [
            "code" => 0,
            "data" => []
        ];
        return json_encode($data);
    }

    public function SendVerificationcode(Request $request)
    {
        // GoChain::getVerificationcode(User::getUser());
        $user_id      = request('user_id', '');
        $user = User::find($user_id);

        Notification::send($user, BaseCode::getCodeNotifyByType(4));
        return $this->success_0(__('api.发送验证码成功'));
    }

}
