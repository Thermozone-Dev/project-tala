<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class DocumentService
{
    public function uploadMeetingDocument(Meeting $meeting, UploadedFile $file): MeetingDocument
    {
        // Store the original file
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('meeting-documents', $filename, 'private');

        // Create document record
        $document = MeetingDocument::create([
            'meeting_id' => $meeting->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
        ]);

        // Parse the DOCX and save as HTML for editing
        $this->parseAndSaveHtml($document);

        // Clean up livewire-temp files after upload
        $this->cleanupLivewireTemp();

        return $document;
    }

    /**
     * Parse DOCX file and save the formatted HTML
     */
    private function parseAndSaveHtml(MeetingDocument $document): void
    {
        try {
            $filePath = Storage::disk('private')->path($document->file_path);

            // Parse the document
            $parser = new WordDocumentParser();
            $html = $parser->parseDocumentFile($filePath);

            if (!empty($html)) {
                // Save HTML to file
                $editedHtmlPath = $document->getEditedHtmlPath();
                $directory = dirname($editedHtmlPath);

                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                file_put_contents($editedHtmlPath, $html);
            }
        } catch (\Exception $e) {
            // Don't fail the upload if parsing fails
        }
    }

    public function deleteDocument(MeetingDocument $document): void
    {
        // Delete original file if it exists
        if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        // Delete edited HTML version if it exists
        $editedPath = $this->getEditedHtmlPath($document);
        if (file_exists($editedPath)) {
            unlink($editedPath);
        }

        // Delete all PDF attachments associated with highlights
        foreach ($document->highlights as $highlight) {
            if ($highlight->pdf_path && Storage::disk('private')->exists($highlight->pdf_path)) {
                Storage::disk('private')->delete($highlight->pdf_path);
            }
        }

        // Delete highlights and record
        $document->highlights()->delete();
        $document->delete();

        // Clean up livewire-temp files
        $this->cleanupLivewireTemp();
    }

    /**
     * Empty livewire-tmp directory after upload
     */
    private function cleanupLivewireTemp(): void
    {
        $livewireTempPath = storage_path('app/livewire-tmp');

        if (is_dir($livewireTempPath)) {
            try {
                File::cleanDirectory($livewireTempPath);
            } catch (\Exception $e) {
                // Silently fail if cleanup fails
            }
        }
    }

    /**
     * Get the path to the edited HTML file for a document
     */
    public function getEditedHtmlPath(MeetingDocument $document): string
    {
        $basePath = Storage::disk('private')->path('meeting-documents');
        return $basePath . '/' . $document->id . '-edited.html';
    }

    /**
     * Get editable content - either from edited HTML file or parse the original Word document
     */
    public function getEditableContent(MeetingDocument $document): string
    {
        // Check if there's an edited version stored as HTML
        $editedHtmlPath = $this->getEditedHtmlPath($document);
        if (file_exists($editedHtmlPath)) {
            return file_get_contents($editedHtmlPath);
        }

        // Parse the original Word file
        if ($document->file_path) {
            $filePath = Storage::disk('private')->path($document->file_path);
            $parser = new WordDocumentParser();
            return $parser->parseDocumentFile($filePath);
        }

        return '';
    }

    /**
     * Save edited content as HTML file
     * Sanitizes with permissive Purifier config to preserve formatting
     */
    public function saveEditedContent(MeetingDocument $document, string $htmlContent): bool
    {
        // Sanitize with permissive config that preserves formatting
        $sanitizedHtml = $this->sanitizeWithPermissiveConfig($htmlContent);

        if (empty(strip_tags($sanitizedHtml))) {
            return false;
        }

        try {
            // Save edited HTML to file
            $editedHtmlPath = $this->getEditedHtmlPath($document);
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
     * Sanitize HTML with permissive config for editing
     */
    private function sanitizeWithPermissiveConfig(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();

        // Allow formatting elements and attributes needed for document editing
        $config->set('HTML.Allowed',
            'div[style],p[style],br,strong,em,u,span[style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],' .
            'ul,ol,li,table[style],tr,td[style],th[style],tbody,thead,' .
            'img[src|alt|width|height|style],a[href|style|target]'
        );

        // Allow comprehensive CSS properties for formatting
        $config->set('CSS.AllowedProperties',
            'text-align,text-indent,color,font-size,font-weight,font-style,text-decoration,' .
            'margin,margin-left,margin-right,margin-top,margin-bottom,' .
            'padding,padding-left,padding-right,padding-top,padding-bottom,' .
            'line-height,border,border-collapse,width,height,' .
            'background-color,vertical-align'
        );

        // Disable auto-formatting that might strip styles
        $config->set('HTML.Trusted', false);
        $config->set('CSS.Trusted', false);

        // Allow data URIs for images and safe URL schemes
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'data' => true]);

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
