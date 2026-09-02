<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentHighlight extends Model
{
    protected $fillable = [
        'meeting_document_id',
        'highlighted_text',
        'start_offset',
        'end_offset',
        'pdf_filename',
        'original_filename',
        'pdf_path',
        'notes',
        'created_by',
        'uploader_id',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(MeetingDocument::class, 'meeting_document_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
