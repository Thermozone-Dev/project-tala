<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendee extends Model
{
    protected $table = 'meeting_attendees';

    protected $fillable = [
        'meeting_id',
        'user_id',
        'attendance_status',
    ];

    /**
     * Attendance status options:
     * pending  — invited, no response yet
     * accepted — confirmed attendance
     * declined — declined the meeting
     */
    const STATUS_PENDING  = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
