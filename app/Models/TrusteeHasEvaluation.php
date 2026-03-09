<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrusteeHasEvaluation extends Model
{
    //

    public $timestamps = false;

    protected $table = 'trustee_has_evaluation';

    protected $fillable = [
        'evaluation_id',
        'ef_id',
        'committee_id',
        'member_id',
        'evaluator_id'
    ];

    public function evaluationPeriod()
    {
        return $this->belongsTo(EvaluationPeriod::class, 'evaluation_period_id');
    }

    public function form()
    {
        return $this->belongsTo(EvaluationForm::class, 'ef_id');
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class, 'committee_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function assesment_answer()
    {
        return $this->hasMany(QuestionaireAnswer::class, 'trustee_evaluation_id');
    }


    public function attendance_answer()
    {
        return $this->hasMany(AttendanceAnswer::class, 'trustee_evaluation_id');
    }


}
