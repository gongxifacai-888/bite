<?php


namespace App\Models\Setting;


use App\Models\Model;
class SettingType extends Model
{
    public $timestamps = false;

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
