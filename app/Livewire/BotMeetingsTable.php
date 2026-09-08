<?php

namespace App\Livewire;

use App\Actions\ViewAgendaModal;
use App\Models\MeetingDocument;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Illuminate\View\View;

class BotMeetingsTable extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithSchemas;
    use InteractsWithActions;


    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isExecutive = get_executive_role($user->roles->first()?->name);

        return $table
            ->query(
                MeetingDocument::query()
                    ->whereHas('meeting', function (Builder $meetingQuery) use ($user, $isExecutive) {
                        $meetingQuery->whereNull('committee_id');

                        // Non-executive users only see meetings they attended
                        if (!$isExecutive) {
                            $meetingQuery->whereHas('attendees', function (Builder $attendeeQuery) use ($user) {
                                $attendeeQuery->where('user_id', $user->id);
                            });
                        }
                    })
                    ->with('meeting', 'uploadedBy')
            )
            ->columns([
                TextColumn::make('meeting.title')
                    ->label('Meeting Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('meeting.scheduled_at')
                    ->label('Meeting Date')
                    ->dateTime('M d, Y H:i A')
                    ->sortable(),
                TextColumn::make('uploadedBy.full_name')
                    ->label('Uploaded By')
                    ->searchable(['first_name', 'last_name']),
            ])
            ->recordActions([
                ViewAgendaModal::make(),
                Action::make('viewMeeting')
                    ->label('View Meeting')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn (MeetingDocument $record) => \App\Filament\Resources\Meetings\MeetingResource::getUrl('view', ['record' => $record->meeting_id])),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    public function render() : View
    {
        return view('livewire.bot-meetings-table');
    }
}
