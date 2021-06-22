<?php


namespace App\Models\Admin;

use App\Exceptions\ThrowException;
use App\Models\Model;

use Illuminate\Support\Collection;

class AdminModuleType extends Model
{
    public $timestamps = false;

    protected $appends = [
        'module_names'
    ];

    /**类型验重
     *
     * @param $value
     *
     * @throws \Exception
     */
    public function setNameAttribute($value)
    {
        $moduleType = self::where('name', $value)->first();
        if ($this->exists && $moduleType && $moduleType->id != $this->id) {
            throw new ThrowException(__('model.此权限分类已存在'));
        }
        $this->attributes['name'] = $value;
    }

    public function module()
    {
        return $this->hasMany(AdminModule::class);
    }

    public function getModuleNamesAttribute()
    {
        /**@var Collection $module_list * */
        $module_list = $this->module;
        return $module_list->pluck('name')->implode(',');
    }
}
