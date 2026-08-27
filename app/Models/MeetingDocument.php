<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingDocument extends Model
{
    protected $fillable = [
        'meeting_id',
        'filename',
        'original_filename',
        'title',
        'uploaded_by',
        'file_path',
        'file_size',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(DocumentHighlight::class);
    }
}
