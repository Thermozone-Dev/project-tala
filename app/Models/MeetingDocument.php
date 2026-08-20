<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MeetingDocument extends Model
{
    protected $fillable = [
        'meeting_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(DocumentHighlight::class);
    }

    public function getEditedHtmlPath(): string
    {
        $basePath = Storage::disk('private')->path('meeting-documents');
        return $basePath . '/' . $this->id . '-edited.html';
    }

    /**
     * Save edited content as HTML file
     * Sanitizes with Purifier, styling handled via CSS classes
     */
    public function saveEditedContent(string $htmlContent): bool
    {
        // Sanitize the HTML with Purifier
        $sanitizedHtml = \Mews\Purifier\Facades\Purifier::clean($htmlContent, 'default');

        if (empty(strip_tags($sanitizedHtml))) {
            return false;
        }

        try {
            // Save edited HTML to file
            $editedHtmlPath = $this->getEditedHtmlPath();
            $directory = dirname($editedHtmlPath);

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($editedHtmlPath, $sanitizedHtml);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get editable content - either from edited HTML file or parse the original Word document
     */
    public function getEditableContent(): string
    {
        // Check if there's an edited version stored as HTML
        $editedHtmlPath = $this->getEditedHtmlPath();
        if (file_exists($editedHtmlPath)) {
            return file_get_contents($editedHtmlPath);
        }

        // Parse the original Word file
        $filePath = Storage::disk('private')->path($this->file_path);
        $parser = new \App\Services\WordDocumentParser();
        return $parser->parseDocumentFile($filePath);
    }
}
