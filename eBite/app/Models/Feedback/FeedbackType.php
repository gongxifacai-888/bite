<?php

namespace App\Models\Feedback;

use App\Models\Model;

class FeedbackType extends Model
{
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

}
