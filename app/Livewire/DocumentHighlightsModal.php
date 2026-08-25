<?php

namespace App\Livewire;

use App\Models\MeetingDocument;
use App\Services\DocumentService;
use Livewire\Component;

class DocumentHighlightsModal extends Component
{
    public MeetingDocument $document;

    public function mount(MeetingDocument $document): void
    {
        $this->document = $document;
    }

    public function getDocumentHtml(): string
    {
        $documentService = new DocumentService();
        return $documentService->getEditableContent($this->document);
    }

    public function render()
    {
        return view('livewire.document-highlights-modal', [
            'document' => $this->document,
            'htmlContent' => $this->getDocumentHtml(),
        ]);
    }
}
