<?php

namespace App\Actions;

use App\Models\MeetingDocument;
use App\Services\DocumentService;
use Filament\Actions;
use Illuminate\Support\Facades\View;

class ViewAgendaModal
{
    public static function make(?int $meetingDocumentId = null)
    {
        return Actions\Action::make('viewDocument')
            ->label('View')
            ->icon('heroicon-o-eye')
            ->visible(fn (MeetingDocument $record) => (!$record->is_published && !get_executive_role() ? false : true))
            ->modalContent(function (MeetingDocument $record) use ($meetingDocumentId) {
                $documentService = new DocumentService();
                $htmlContent = $documentService->getEditableContent($record);
                return View::make('livewire.document-highlights-modal', [
                    'document' => $record,
                    'htmlContent' => $htmlContent,
                    'meetingDocumentId' => $meetingDocumentId,
                ]);
            })
            ->modalCancelActionLabel(fn () => 'Close')
            ->modalSubmitAction(false)
            ->modalHeading(fn (MeetingDocument $record) => $record->title ?? $record->original_filename);
    }
}
