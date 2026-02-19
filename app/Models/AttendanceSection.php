<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'section_id',
        'show_total_meetings',
        'show_physically_present',
        'show_considered_present',
        'show_total_present',
        'show_attendance_rating',
    ];

    public function section()
    {
        return $this->belongsTo(EvaluationFormSection::class, 'section_id');
    }

    public function meetings()
    {
        return $this->hasMany(AttendanceMeeting::class);
    }
}
