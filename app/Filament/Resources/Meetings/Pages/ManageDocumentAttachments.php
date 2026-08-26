<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\MeetingDocument;
use App\Models\DocumentHighlight;
use App\Services\DocumentService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ManageDocumentAttachments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = MeetingResource::class;
    protected string $view = 'filament.resources.meetings.pages.manage-document-attachments';

    public MeetingDocument $document;

    public function mount(MeetingDocument $document): void
    {
        $this->document = $document;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(DocumentHighlight::query()->where('meeting_document_id', $this->document->id))
            ->columns([
                Tables\Columns\TextColumn::make('highlighted_text')
                    ->label('Highlighted Text')
                    ->limit(50)
                    ->url(fn (DocumentHighlight $record) => route('documents.open-pdf', [
                        'document' => $this->document->id,
                        'highlight' => $record->id,
                    ]))
                    ->openUrlInNewTab()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(60)
                    ->default('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('replace')
                    ->label('Replace PDF')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('pdf_file')
                            ->label('New PDF File')
                            ->disk('private')
                            ->directory('document-pdfs')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(52428800) // 50MB in bytes
                            ->required()
                            ->rules([
                                'required',
                                'file',
                                'mimes:pdf',
                                'max:51200', // 50MB in KB for Laravel validation
                            ])
                            ->helperText('Max file size: 50 MB'),
                    ])
                    ->action(function (DocumentHighlight $record, array $data) {
                        $newFilePath = $data['pdf_file'];

                        if ($newFilePath) {
                            // Delete old PDF file
                            if ($record->pdf_path && Storage::disk('private')->exists($record->pdf_path)) {
                                Storage::disk('private')->delete($record->pdf_path);
                            }

                            // Get the filename from the path
                            $filename = basename($newFilePath);

                            // Update database record with the new file path
                            $record->update([
                                'pdf_filename' => $filename,
                                'pdf_path' => $newFilePath,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('PDF Replaced')
                                ->body('The attachment has been successfully replaced.')
                                ->send();
                        }
                    }),
                Actions\DeleteAction::make()
                    ->label('Delete')
                    ->action(function (DocumentHighlight $record) {
                        $documentService = new DocumentService();

                        // Delete PDF file
                        if ($record->pdf_path && Storage::disk('private')->exists($record->pdf_path)) {
                            Storage::disk('private')->delete($record->pdf_path);
                        }

                        // Remove anchor from document HTML
                        $htmlPath = $documentService->getEditedHtmlPath($this->document);
                        if (file_exists($htmlPath)) {
                            $htmlContent = file_get_contents($htmlPath);

                            // Find and remove the anchor tag with this highlight's text
                            $highlightUrl = route('documents.open-pdf', [
                                'document' => $this->document->id,
                                'highlight' => $record->id,
                            ]);

                            // Remove the anchor tag - replace <a href="...">text</a> with just text
                            $pattern = preg_quote($highlightUrl, '/');
                            $htmlContent = preg_replace(
                                '/<a[^>]*href=["\']' . $pattern . '["\'][^>]*>([^<]*)<\/a>/i',
                                '$1',
                                $htmlContent
                            );

                            file_put_contents($htmlPath, $htmlContent);
                        }

                        // Delete the database record
                        $record->delete();
                    })
            ])
            ->paginated(false)
            ->defaultPaginationPageOption(10);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->url(fn() => MeetingResource::getUrl('edit', ['record' => $this->document->meeting_id]))
                ->color('gray'),
        ];
    }
}
