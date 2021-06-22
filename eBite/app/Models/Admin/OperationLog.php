<?php


namespace App\Models\Admin;

use App\Models\Model;

class OperationLog extends Model
{
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function getDataAttribute()
    {
        $value = $this->attributes['data'];
        return json_encode(unserialize($value));
    }
}
