<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function sections()
    {
        return $this->hasMany(EvaluationFormSection::class);
    }
}
