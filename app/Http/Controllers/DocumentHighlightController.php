<?php

namespace App\Http\Controllers;

use App\Models\DocumentHighlight;
use App\Models\MeetingDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class DocumentHighlightController extends Controller
{
    public function attachPdf(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:meeting_documents,id',
            'highlighted_text' => 'required|string|max:1000',
            'pdf_file' => 'required|file|mimes:pdf|max:128000', // 125MB in KB
            'notes' => 'nullable|string|max:500',
        ], [
            'pdf_file.max' => 'The PDF file cannot exceed 125MB.',
            'pdf_file.mimes' => 'Only PDF files are allowed.',
            'pdf_file.required' => 'Please select a PDF file to attach.',
        ]);

        // Security: Check for malicious patterns in highlighted text
        if (preg_match('/<script|onerror|onclick|javascript:|on\w+\s*=/i', $request->highlighted_text)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid characters detected in highlighted text',
            ], 422);
        }

        $document = MeetingDocument::findOrFail($request->document_id);

        // Store the PDF
        $filename = uniqid() . '_' . time() . '.pdf';
        $path = $request->file('pdf_file')->storeAs('document-pdfs', $filename, 'private');

        // Sanitize text and notes before storing
        $sanitizedText = htmlspecialchars($request->highlighted_text, ENT_QUOTES, 'UTF-8');
        $sanitizedNotes = Purifier::clean($request->notes ?? '', 'default');

        // Create highlight record
        $highlight = DocumentHighlight::create([
            'meeting_document_id' => $document->id,
            'highlighted_text' => $sanitizedText,
            'start_offset' => 0,
            'end_offset' => strlen($request->highlighted_text),
            'pdf_filename' => $filename,
            'pdf_path' => $path,
            'notes' => $sanitizedNotes,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'highlight_id' => $highlight->id,
            'message' => 'PDF attached successfully',
        ]);
    }

    public function openPdf(MeetingDocument $document, DocumentHighlight $highlight)
    {
        if (!Storage::disk('private')->exists($highlight->pdf_path)) {
            abort(404, 'PDF file not found');
        }

        return Storage::disk('private')->response($highlight->pdf_path);
    }

    public function deleteAttachment(Request $request)
    {
        $request->validate([
            'highlight_id' => 'required|exists:document_highlights,id',
            'document_id' => 'required|exists:meeting_documents,id',
        ]);

        $highlight = DocumentHighlight::findOrFail($request->highlight_id);
        $document = MeetingDocument::findOrFail($request->document_id);

        // Verify the highlight belongs to this document
        if ($highlight->meeting_document_id !== $document->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Delete the PDF file from storage
        if ($highlight->pdf_path && Storage::disk('private')->exists($highlight->pdf_path)) {
            Storage::disk('private')->delete($highlight->pdf_path);
        }

        // Remove the anchor tag from the edited HTML
        $this->removeAnchorFromDocument($document, $highlight);

        // Delete the highlight record
        $highlight->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment removed successfully',
        ]);
    }

    /**
     * Remove anchor tag from document HTML
     */
    private function removeAnchorFromDocument(MeetingDocument $document, DocumentHighlight $highlight): void
    {
        try {
            $basePath = Storage::disk('private')->path('meeting-documents');
            $editedHtmlPath = $basePath . '/' . $document->id . '-edited.html';

            if (file_exists($editedHtmlPath)) {
                $content = file_get_contents($editedHtmlPath);

                // Remove only the anchor tag for this specific highlight using its unique ID in the URL
                // Matches: <a href="..../highlight/{highlight_id}/pdf...">any text</a>
                // Uses the highlight ID to target the exact anchor
                $highlightId = $highlight->id;
                $pattern = '/<a\s+[^>]*href="[^"]*highlight\/' . preg_quote($highlightId) . '\/pdf[^"]*"[^>]*>(.+?)<\/a>/si';

                $newContent = preg_replace($pattern, '$1', $content);

                // Only write if something was actually replaced
                if ($newContent !== $content) {
                    file_put_contents($editedHtmlPath, $newContent);
                }
            }
        } catch (\Exception $e) {
            // Log but don't fail - the highlight record still gets deleted
            \Log::warning('Error removing anchor from document', [
                'document_id' => $document->id,
                'highlight_id' => $highlight->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
