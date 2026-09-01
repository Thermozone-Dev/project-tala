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

        $this->record->attendees()->where('user_id',auth()->user()->id)
            ->update([
                'seen_at' => now()
            ]);

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
        $attendees = $this->record->attendees->where('seen_at','!=', null)->sortByDesc('seen_at');

        return [
            Action::make('seen_by')
                ->authorize(auth()->user()->can('Update:Meeting'))
                ->tooltip('Seen by')
                ->label(fn() => 'Seen by '.$attendees->count().' attendee'.($attendees->count() > 1 ? 's' : null))
                ->modalContent(function() use ($attendees) {
                    return view('filament.resources.meetings.pages.seen-by-table', ['attendees' => $attendees]);
                })
                ->hiddenLabel()
                ->icon(Heroicon::OutlinedEye)
                ->action(function () {})
                ->modalFooterActions([]),
            EditAction::make(),
        ];
    }
}
