<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingScale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
    ];

    public function values()
    {
        return $this->hasMany(RatingScaleValue::class);
    }

    public function sections()
    {
        return $this->hasMany(EvaluationFormSection::class);
    }
}
