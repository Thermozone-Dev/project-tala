<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\Meeting;
use App\Models\MeetingDocument;
use App\Services\DocumentService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageDocuments extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = MeetingResource::class;
    protected string $view = 'filament.resources.meetings.pages.manage-documents';
    public Meeting $meeting;

    public function mount(Meeting $record): void
    {
        $this->meeting = $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload Document')
                ->form([
                    FileUpload::make('document')
                        ->label('Upload Word Document (.docx, .doc)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'])
                        ->required()
                        ->disk('private')
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    $uploadedFiles = $data['document'] ?? [];

                    if (is_array($uploadedFiles) && !empty($uploadedFiles)) {
                        $file = $uploadedFiles[0];
                    } else {
                        $file = $uploadedFiles;
                    }

                    if ($file && $file instanceof \Illuminate\Http\UploadedFile) {
                        $documentService = new DocumentService();
                        $document = $documentService->uploadMeetingDocument($this->meeting, $file);

                        Notification::make()
                            ->title('Document Uploaded')
                            ->body("Document '{$document->original_filename}' uploaded successfully!")
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MeetingDocument::query()->where('meeting_id', $this->meeting->id))
            ->columns([
                TextColumn::make('original_filename')
                    ->label('File Name')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn (MeetingDocument $record) => view('livewire.document-highlights-modal', [
                        'document' => $record,
                    ]))
                    ->modalHeading(fn (MeetingDocument $record) => $record->original_filename)
                    ->modalSubmitActionLabel('Close'),
                Action::make('manage-attachments')
                    ->label('Attachments')
                    ->icon('heroicon-o-link')
                    ->badge(fn (MeetingDocument $record) => $record->highlights()->count())
                    ->badgeColor('info')
                    ->url(fn (MeetingDocument $record) => MeetingResource::getUrl('manage-document-attachments', ['document' => $record])),
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (MeetingDocument $record) => MeetingResource::getUrl('edit-document', ['record' => $this->meeting, 'document' => $record])),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (MeetingDocument $record) {
                        $documentService = new DocumentService();
                        $documentService->deleteDocument($record);

                        Notification::make()
                            ->title('Document Deleted')
                            ->body("Document '{$record->original_filename}' has been deleted.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->action(function () {
                            $records = $this->getSelectedTableRecords();
                            $documentService = new DocumentService();
                            $count = 0;

                            foreach ($records as $record) {
                                $documentService->deleteDocument($record);
                                $count++;
                            }

                            Notification::make()
                                ->title('Documents Deleted')
                                ->body("$count document(s) and all associated files have been deleted.")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
