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
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
            'notes' => 'nullable|string|max:500',
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
}
