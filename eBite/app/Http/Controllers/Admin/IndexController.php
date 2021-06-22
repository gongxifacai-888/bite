<?php


namespace App\Http\Controllers\Admin;

use App\BlockChain\BlockChain;
use App\Models\Feedback\Feedback;
use App\Models\User\User;

class IndexController extends Controller
{
    public function index()
    {
        return view('admin.index.index');
    }

    public function home()
    {
        //用户总数
        $user_count = User::count();
        //今日登陆
        $today_login = User::where('last_login_at', '>=', today())->count();
        //今日注册
        $today_register = User::where('created_at', '>=', today())->count();
        //今日反馈未回复
        $today_feedback = Feedback::where('created_at', '>=', today())->where('is_replied', 0)->count();

        return view('admin.index.home', [
            'user_count' => $user_count,
            'today_login' => $today_login,
            'today_register' => $today_register,
            'today_feedback' => $today_feedback,
        ]);
    }

    /**
     * 获取安全验证码
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authCode()
    {
        $host = BlockChain::getNodeServerHost();
        $url = "{$host}/v3/wallet/verification";
        $response = http($url, [], 'POST');
        if ($response['code'] != 0) {
            return $this->error($response['msg']);
        }
        return $this->success('已发送验证码', $response);
    }
}
