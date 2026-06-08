<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Gate;

class Meeting extends Model
{
    protected $table = 'meetings';

    protected $fillable = [
        'title',
        'meeting_url',
        'description',
        'scheduled_at',
        'duration_minutes',
        'meeting_status_id',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(MeetingStatus::class, 'meeting_status_id');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'meeting_attendees',
            'meeting_id',
            'user_id'
        )
            ->withPivot('attendance_status')
            ->withTimestamps()
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', [
                'Super Admin',
                'Secretariat',
            ]));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOwnedOrAssigned(Builder $query): void
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('meetings.created_by', auth()->user()->id)
                ->orWhereRelation('attendees', 'meeting_attendees.user_id', auth()->user()->id);
        }
    }

    public function getStartAttribute(): string
    {
        return $this->scheduled_at->toIso8601String();
    }

    public function getEndAttribute(): string
    {
        return $this->scheduled_at->copy()->addMinutes((int) $this->duration_minutes)->toIso8601String();
    }
}
