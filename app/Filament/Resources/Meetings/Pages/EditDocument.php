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
            Action::make('set_active')
                ->label(fn () => $this->document->is_published ? 'Unpublish' : 'Publish')
                ->icon(fn () => $this->document->is_published ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->action(function () {
                    $this->document->update([
                        'is_published' => !$this->document->is_published,
                    ]);

                    activity()
                        ->performedOn($this->document)
                        ->causedBy(auth()->user())
                        ->event($this->document->is_published ? 'published' : 'unpublished')
                        ->log('Document ' . ($this->document->is_published ? 'published' : 'unpublished'));

                    $status = $this->document->is_published ? 'published' : 'unpublished';
                    Notification::make()
                        ->title(ucfirst($status))
                        ->body("Document has been {$status}.")
                        ->success()
                        ->send();

                    $this->redirect(request()->header('Referer') ?? MeetingResource::getUrl('edit', ['record' => $this->meeting]));
                })
                ->color(fn () => $this->document->is_published ? 'danger' : 'success'),

            Action::make('back')
                ->label('Back')
                ->url(MeetingResource::getUrl('edit', ['record' => $this->meeting]))
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

        activity()
            ->performedOn($this->document)
            ->causedBy(auth()->user())
            ->event('updated')
            ->log('Document updated');

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
