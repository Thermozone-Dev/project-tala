<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Actions\ViewAgendaModal;
use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\MeetingDocument;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;

class MeetingsRelationManager extends RelationManager
{
    protected static string $relationship = 'meetings';

    protected static ?string $recordTitleAttribute = 'title';


    protected static ?string $title = 'Meeting Agenda';


    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isExecutive = get_executive_role($user->roles->first()?->name);

        return $table
            ->query(function (Builder $query) use ($isExecutive, $user) {
                // Get all meeting documents for this committee's meetings
                $meetingQuery = $this->getOwnerRecord()
                    ->meetings()
                    ->with('documents');

                // For non-executive users, only show meetings they attended
                if (!$isExecutive) {
                    $meetingQuery->whereHas('attendees', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }

                $meetingIds = $meetingQuery->pluck('id');

                return MeetingDocument::whereIn('meeting_id', $meetingIds);
            })
            ->columns([
                Tables\Columns\TextColumn::make('meeting.title')
                    ->label('Meeting Title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Agenda Title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('meeting.scheduled_at')
                    ->label('Meeting Date')
                    ->dateTime('M d, Y H:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('uploadedBy.full_name')
                    ->label('Uploaded By')
                    ->searchable(['first_name', 'last_name']),
            ])
            ->actions([
                ViewAgendaModal::make(),
                Actions\Action::make('viewMeeting')
                    ->label('View Meeting')
                    ->color('info')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (MeetingDocument $record) => MeetingResource::getUrl('view', ['record' => $record->meeting_id])),
            ])
            ->bulkActions([])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
