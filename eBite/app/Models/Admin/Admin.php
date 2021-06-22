<?php


namespace App\Models\Admin;

use App\Models\User\User;
use App\Exceptions\ThrowException;

class Admin extends User
{
    protected $appends = [];

    protected $hidden=[
        'google_secret',
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(AdminRole::class);
    }

    public static function getAdminId()
    {
        return session('admin_id', 0);
    }

    public static function getAdmin()
    {
        return self::find(self::getAdminId());
    }

    /**
     * @param $password
     *
     * @return string|void
     * @throws \Exception
     */
    public function login($password)
    {
        if (!$this->exists) {
            throw new ThrowException(__('model.用户不存在'));
        }
        if ($this->password != $this->encryptPassword($password)) {
            throw new ThrowException(__('model.密码错误'));
        }
        $this->last_login_ip = request()->ip();
        $this->last_login_at = now();
        $this->save();
        session()->put('admin_id', $this->id);
    }

    /**管理员注册
     *
     * @param string $username
     * @param string $password
     * @param string $role_id
     * @param string $invite_code
     * @param string $area_id
     *
     * @return Admin|User
     * @throws \Exception
     */
    public static function register($username, $password, $role_id, $invite_code = '', $area_id = '')
    {

        if (self::where('username', $username)->exists()) {
            throw new ThrowException(__('model.此用户已存在'));
        }

        $admin = new self([
            'username' => $username,
            'password' => $password,
            'role_id' => $role_id,
        ]);
        $admin->save();

        return $admin;
    }


}
