<?php
/**
 * create by vscode
 *
 * @author lion
 */

namespace App\Models\News;

use App\Models\Model;
class NewsCategory extends Model
{

    public function news()
    {
        return $this->hasMany(News::class);
    }

}
