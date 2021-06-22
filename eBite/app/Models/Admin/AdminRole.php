<?php

namespace App\Models\Admin;

use App\Models\Model;

class AdminRole extends Model
{
    public $timestamps = false;

    /**获取角色能操作的模块
     *
     */
    public function adminRoleModule()
    {
        return $this->hasMany(AdminRoleModule::class);
    }
}
