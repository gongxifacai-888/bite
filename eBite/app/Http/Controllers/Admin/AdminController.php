<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/19
 * Time: 17:04
 */

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Exceptions\ThrowException;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.admin.login', [
            'admin_redirect_uri' => session('admin_redirect_uri', '/admin/index/index')
        ]);
    }

    public function doLogin()
    {
        $username = request('username', '');
        $password = request('password', '');

        try {
            /**@var $admin Admin* */
            $admin = Admin::where('username', $username)->first();
            if (!$admin) {
                throw new ThrowException('此用户不存在');
            }

            $admin->login($password);
            session()->forget('admin_redirect_uri');

            return $this->success('登陆成功');
        } catch (\Throwable $t) {
            return $this->error($t->getMessage());
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('/admin');
    }

    public function index()
    {
        return view('admin.admin.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = Admin::with(['role'])->orderBy('id', 'DESC')->paginate($limit);

        return $this->layuiPageData($list);
    }

    public function add()
    {
        $role_list = AdminRole::get();
        return view('admin.admin.add', [
            'role_list' => $role_list
        ]);
    }

    public function save()
    {
        $username = request('username', '');
        $password = request('password', '');
        $role_id = request('role_id', 0);

        if (Admin::where('username', $username)->exists()) {
            return $this->error('此用户已存在');
        }

        $super_admin_google_secret = Admin::where('username', 'admin')->value('google_secret');

        Admin::create([
            'username' => $username,
            'password' => $password,
            'role_id' => $role_id,
            'google_secret' => $super_admin_google_secret,
        ]);

        return $this->success('保存成功');
    }

    public function edit()
    {
        $id = request('id', 0);
        $info = Admin::find($id);
        $role_list = AdminRole::get();
        return view('admin.admin.edit', [
            'info' => $info,
            'role_list' => $role_list
        ]);
    }

    public function update()
    {
        $id = request('id', 0);
        $password = request('password', '');
        $role_id = request('role_id', 0);

        $admin = Admin::find($id);

        if ($password) {
            $admin->password = $password;
        }
        $admin->role_id = $role_id;
        $admin->save();

        return $this->success('保存成功');
    }

    public function delete()
    {
        $id = request('id', 0);
        $admin = Admin::find($id);
        if ($admin->username == 'admin') {
            return $this->error('系统默认管理员不允许删除');
        }
        $admin->delete();
        return $this->success('删除成功');
    }

}
