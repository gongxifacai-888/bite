<?php

namespace App\Models\Admin;

use App\Models\Model;

class AdminModule extends Model
{
    public $timestamps = false;

    protected $appends = [
        'type_name'
    ];

    public function action()
    {
        return $this->hasMany(AdminModuleAction::class);
    }

    public function getTypeNameAttribute()
    {
        return $this->adminModuleType->setAppends([])->name ?? __('model.未知');
    }

    public function adminModuleType()
    {
        return $this->belongsTo(AdminModuleType::class);
    }

}
