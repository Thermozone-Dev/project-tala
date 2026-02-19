<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationFormSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'evaluation_form_id',
        'rating_scale_id',
        'section_type_id',
        'title',
        'add_remarks',
    ];

    public function evaluationForm()
    {
        return $this->belongsTo(EvaluationForm::class);
    }

    public function ratingScale()
    {
        return $this->belongsTo(RatingScale::class);
    }

    public function sectionType()
    {
        return $this->belongsTo(SectionType::class);
    }

    public function questionnaires()
    {
        return $this->hasMany(Questionnaire::class, 'section_id');
    }

    public function attendanceSection()
    {
        return $this->hasOne(AttendanceSection::class, 'section_id');
    }
}
