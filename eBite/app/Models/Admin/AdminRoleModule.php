<?php

namespace App\Models\Admin;

use App\Models\Model;

class AdminRoleModule extends Model
{
    public $timestamps = false;

    public function module()
    {
        return $this->belongsTo(AdminModule::class);
    }
}
