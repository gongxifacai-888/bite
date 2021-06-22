<?php


namespace App\Http\Controllers\Admin;


use App\Models\Admin\Admin;
use App\Models\Setting\Setting;
use App\Utils\GoogleAuth;

class GoogleAuthController extends Controller
{
    public function index()
    {
        return view('admin.googleAuth.index');
    }

    public function qrCode()
    {
        $password = request('password', '');

        /**@var Admin $admin * */
        $admin = Admin::getAdmin();

        if (!$admin->checkPassword($password)) {
            return $this->error('密码错误');
        }

        $googleAuth = new GoogleAuth();

        $secret = $googleAuth->createSecret();
        $admin->update(['google_secret' => $secret]);

        $qr_code_url = $googleAuth->getQRCodeGoogleUrl($admin->username, $secret);

        return $this->success('请求成功', [
            'qr_code_url' => $qr_code_url,
            'secret' => $secret
        ]);
    }


}
