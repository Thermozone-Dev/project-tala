<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MeetingAttendee extends Model
{
    use LogsActivity;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'committee_id',
        'attendance_status_id',
        'is_late',
        'mark_timestamp',
        'updated_by',
    ];

    protected $casts = [
        'is_late'        => 'integer',
        'mark_timestamp' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'attendance_status_id',
                'is_late',
                'mark_timestamp',
                'updated_by',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Attendance {$eventName}");
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function attendanceStatus(): BelongsTo
    {
        return $this->belongsTo(MeetingAttendanceStatus::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
