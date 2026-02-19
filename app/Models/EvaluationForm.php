<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationForm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pdf_template_id',
        'shortcode',
        'title',
    ];

    public function pdfTemplate()
    {
        return $this->belongsTo(PdfTemplate::class);
    }

    public function sections()
    {
        return $this->hasMany(EvaluationFormSection::class);
    }
}
