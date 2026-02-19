<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'section_id',
        'name',
    ];

    public function section()
    {
        return $this->belongsTo(EvaluationFormSection::class, 'section_id');
    }
}
