<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AttendanceAnswer extends Model
{
    use HasFactory,LogsActivity;

    protected $table = 'attendance_answer';

    public $timestamps = false; // remove if you add timestamps later

    protected $fillable = [
        'total_meetings',
        'physically_present',
        'considered_present',
        'total_present',
        'attendance_rating_scale_values_id',
        'meeting_id',
        'trustee_evaluation_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function meeting()
    {
        return $this->belongsTo(AttendanceMeeting::class, 'meeting_id');
    }


    public function ratingScaleValue()
    {
        return $this->belongsTo(RatingScaleValue::class, 'attendance_rating_scale_values_id');
    }

    public function trusteeEvaluation()
    {
        return $this->belongsTo(TrusteeHasEvaluation::class, 'trustee_evaluation_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'total_meetings',
                'physically_present',
                'considered_present',
                'total_present',
                'ratingScaleValue.value',
                'ratingScaleValue.qualitative',
                'ratingScaleValue.name',
            ]);
    }

}
