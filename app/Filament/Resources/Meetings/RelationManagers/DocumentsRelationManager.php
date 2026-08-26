<?php

namespace App\Filament\Resources\Meetings\RelationManagers;

use App\Models\MeetingDocument;
use App\Services\DocumentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'original_filename';

    protected static ?string $title = 'Attachments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_filename')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('original_filename')
                    ->label('File Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('uploadedBy.full_name')
                    ->label('Uploaded By')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('upload')
                    ->authorize( fn () => auth()->user()->can("ManageMeetingDocuments"))
                    ->label('Upload Word File')
                    ->form([
                        TextInput::make('title')
                            ->label('Document Title')
                            ->required()
                            ->placeholder('Enter document title'),
                        FileUpload::make('document')
                            ->label('Upload Word Document (.docx)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->required('Please select a document to upload.')
                            ->disk('private')
                            ->storeFiles(false)
                            ->validationMessages([
                                'document.mimes' => 'The system only accepts .docx format.',
                            ]),
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
                            $document = $documentService->uploadMeetingDocument($this->getOwnerRecord(), $file);

                            // Set title and uploaded_by
                            $document->update([
                                'title' => $data['title'],
                                'uploaded_by' => Auth::id(),
                            ]);

                            Notification::make()
                                ->title('Document Uploaded')
                                ->body("Document '{$document->original_filename}' uploaded successfully!")
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (MeetingDocument $record) {
                        $documentService = new DocumentService();
                        $htmlContent = $documentService->getEditableContent($record);
                        return View::make('livewire.document-highlights-modal', [
                            'document' => $record,
                            'htmlContent' => $htmlContent,
                        ]);
                    })
                    ->modalCancelActionLabel(fn () => 'Close')
                    ->modalSubmitAction(false)
                    ->modalHeading(fn (MeetingDocument $record) => $record->original_filename),

                 Action::make('edit')
                    ->label('Edit ')
                    ->icon('heroicon-o-pencil')
                    ->authorize( fn () => auth()->user()->can("ManageMeetingDocuments"))
                    ->url(fn (MeetingDocument $record) => url()->route('filament.admin.resources.meetings.edit-document', ['record' => $this->getOwnerRecord(), 'document' => $record])),

                Action::make('manage-attachments')
                    ->label('PDF Attachments')
                    ->icon('heroicon-o-link')
                    ->authorize( fn () => auth()->user()->can("ManageMeetingDocuments"))
                    ->badge(fn (MeetingDocument $record) => $record->highlights()->count())
                    ->badgeColor('info')
                    ->url(fn (MeetingDocument $record) => url()->route('filament.admin.resources.meetings.manage-document-attachments', ['document' => $record])),
                DeleteAction::make()
                    ->authorize( fn () => auth()->user()->can("ManageMeetingDocuments"))
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize( fn () => auth()->user()->can("ManageMeetingDocuments"))
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
