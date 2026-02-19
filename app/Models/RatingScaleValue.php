<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingScaleValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rating_scale_id',
        'name',
        'value',
        'qualitative',
    ];

    public function ratingScale()
    {
        return $this->belongsTo(RatingScale::class);
    }
}
