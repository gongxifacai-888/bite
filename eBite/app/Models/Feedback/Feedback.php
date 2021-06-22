<?php

namespace App\Models\Feedback;

use App\Models\Model;
use App\Models\User\User;
class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $appends = [
        'type_name',
    ];

    public function feedbackType()
    {
        return $this->belongsTo(FeedbackType::class,'type_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getTypeNameAttribute()
    {
        return $this->feedbackType()->value('name');
    }
}
