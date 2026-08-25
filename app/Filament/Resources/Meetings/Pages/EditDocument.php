<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\Meeting;
use App\Models\MeetingDocument;
use App\Services\DocumentService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class EditDocument extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = MeetingResource::class;
    protected string $view = 'filament.resources.meetings.pages.edit-document';

    public Meeting $meeting;
    public MeetingDocument $document;

    public function mount(Meeting $record, MeetingDocument $document): void
    {
        $this->meeting = $record;
        $this->document = $document;
    }

    public function getDocumentContent(): string
    {
        $documentService = new DocumentService();
        return $documentService->getEditableContent($this->document);
    }

    public function getTitle(): string|Htmlable
    {
        return "Edit: {$this->document->original_filename}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Document')
                ->color('success')
                ->icon('heroicon-o-check')
                ->action('handleSaveAction'),
            Action::make('back')
                ->label('Back')
                ->url(MeetingResource::getUrl('manage-documents', ['record' => $this->meeting]))
                ->color('gray'),
        ];
    }

    public function handleSaveAction(): void
    {
        // This will be called via JavaScript that gets the content from TinyMCE
    }

    public function saveDocument(string $content = ''): void
    {
        if (empty($content)) {
            Notification::make()
                ->title('Error')
                ->body('No content to save')
                ->danger()
                ->send();
            return;
        }

        $documentService = new DocumentService();
        $success = $documentService->saveEditedContent($this->document, $content);

        if ($success) {
            Notification::make()
                ->title('Document Saved')
                ->body('Your edits have been saved!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Save Failed')
                ->body('Could not save changes to the document. Please try again.')
                ->danger()
                ->send();
        }
    }
}
