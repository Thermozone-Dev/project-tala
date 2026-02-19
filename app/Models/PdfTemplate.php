<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PdfTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function evaluationForms()
    {
        return $this->hasMany(EvaluationForm::class);
    }
}
