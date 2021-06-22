<?php


namespace App\Models\Setting;


use App\Models\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $timestamps = false;

    protected $appends = [
        'type_name'
    ];

    public function getTypeNameAttribute()
    {
        return $this->settingType->name ?? __('model.未知');
    }

    public function save(array $options = [])
    {
        Cache::set("setting:{$this->key}", $this->value, 60);
        return parent::save($options);
    }

    /**
     * @param        $key
     * @param string $default
     * @param bool   $filter_visible
     *
     * @return mixed|string
     */
    public static function getValueByKey($key, $default = '', $filter_visible = false)
    {
        $value = Cache::remember("setting:{$key}", 60, function () use ($key, $filter_visible) {
            return self::where('key', $key)->when($filter_visible, function ($query) {
                $query->where('visible', 1);
            })->value('value');
        });
        return $value ?? $default;
    }

    public function settingType()
    {
        return $this->belongsTo(SettingType::class);
    }
}
