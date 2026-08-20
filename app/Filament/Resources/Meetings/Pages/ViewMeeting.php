<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewMeeting extends ViewRecord
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageDocuments')
                ->label('Manage Documents')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url(fn () => MeetingResource::getUrl('manage-documents', ['record' => $this->record]))
                ->color('gray'),
            EditAction::make(),
        ];
    }
}
