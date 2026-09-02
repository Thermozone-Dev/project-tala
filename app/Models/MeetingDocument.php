<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;


class MeetingDocument extends Model
{
    use LogsActivity;
    protected $fillable = [
        'meeting_id',
        'filename',
        'original_filename',
        'title',
        'uploaded_by',
        'file_path',
        'file_size',
        'is_published',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }


    protected function scopePublished(Builder $query): void
    {
        $query->where('is_published', 1);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(DocumentHighlight::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'meeting_id',
                'filename',
                'original_filename',
                'title',
                'uploaded_by',
                'file_path',
                'file_size'
            ]);
    }
}
