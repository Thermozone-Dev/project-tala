<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TrusteeHasEvaluation extends Model
{
    //
    use SoftDeletes, LogsActivity;
    public $timestamps = false;

    protected $table = 'trustee_has_evaluation';

    protected $fillable = [
        'evaluation_id',
        'ef_id',
        'committee_id',
        'member_id',
        'evaluator_id',
        'trustee_evaluation_statuses_id'
    ];

    public function evaluationPeriod()
    {
        return $this->belongsTo(EvaluationPeriod::class, 'evaluation_id')->whereNull('deleted_at');
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


    public function other_comments()
    {
        return $this->hasMany(OtherCommentAnswer::class, 'trustee_evaluation_id');
    }

    public function eval_status()
    {
        return $this->belongsTo(TrusteeEvaluationStatus::class, 'trustee_evaluation_statuses_id');
    }


    protected function scopePending(Builder $query): void
    {
        $query->where('trustee_evaluation_statuses_id', 3);
    }


    protected function scopeProgress(Builder $query): void
    {
        $query->where('trustee_evaluation_statuses_id', 1);
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'eval_status.name',
            ]);
    }
}
