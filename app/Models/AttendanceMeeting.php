<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceMeeting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'attendance_section_id',
        'name',
    ];

    public function attendanceSection()
    {
        return $this->belongsTo(AttendanceSection::class);
    }
}
