<?php


namespace App\Models\System;

use App\Models\Model;

class Area extends Model
{
    protected $appends = ['lang_name','payway_name'];

    public function lang()
    {
        return $this->belongsTo(Lang::class);
    }

     public function getPaywayNameAttribute()
     {
         $payways =explode(",",$this->attributes['payways']);
         $collection = Payway::whereIn("id" , $payways)->get();
         $name = "";
         foreach($collection as $pay){
             $name .= "$pay->name, ";
         }
         return trim($name,", ");
     }

    public function getLangNameAttribute()
    {
        return $this->lang->name ?? __('model.未知');
    }

}
