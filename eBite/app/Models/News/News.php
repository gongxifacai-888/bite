<?php
/**
 * create by vscode
 *
 * @author lion
 */

namespace App\Models\News;

use App\Models\Model;
use App\Models\System\Lang;

class News extends Model
{
    protected $appends = [
        'category_name',
        'lang_name',
    ];

    public function getLogoAttribute($logo)
    {
        return url($logo);
    }

    public function getBannerAttribute($banner)
    {
        return url($banner);
    }

    public function getLangNameAttribute()
    {
        return $this->lang()->value('name');
    }

    public function getCategoryNameAttribute()
    {
        return $this->category()->value('name');
    }

    public function category()
    {
        return $this->belongsTo(NewsCategory::class);
    }

    public function lang()
    {
        return $this->belongsTo(Lang::class);
    }
}
