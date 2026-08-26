<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewMeeting extends ViewRecord
{
    protected static string $resource = MeetingResource::class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Check authorization for non-executive users
        $user = auth()->user();
        if (!get_executive_role()) {
            $isAttendee = $this->record->attendees()->where('user_id', $user->id)->exists();

            if (!$isAttendee) {
                Notification::make()
                    ->title('Access Denied')
                    ->body('You do not have permission to view this meeting.')
                    ->danger()
                    ->send();

                redirect()->route('filament.admin.resources.meetings.index');
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
