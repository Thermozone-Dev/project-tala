<?php

namespace App\Filament\Resources\Meetings\RelationManagers;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';

    protected static ?string $relatedResource = MeetingResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Attendees')
            ->columns([
                TextColumn::make('full_name')->label('Name'),
                TextColumn::make('attendance_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->recordActions([
                Action::make('accept')->color('success')->icon(Heroicon::OutlinedCheckCircle)
                    ->visible(fn($record) => $record->attendance_status == 'pending' && $record->user_id == auth()->user()->id)
                    ->requiresConfirmation()
                    ->action(function ($record){
                        $record->pivot->attendance_status = 'accepted';
                        $record->pivot->save();

                        Notification::make()
                            ->title('Meeting Accepted')
                            ->success()
                            ->send();
                    }),
                Action::make('decline')->color('danger')->icon(Heroicon::OutlinedXCircle)
                    ->visible(fn($record) => $record->attendance_status == 'pending' && $record->user_id == auth()->user()->id)
                    ->requiresConfirmation()
                    ->action(function ($record){
                        $record->pivot->attendance_status = 'declined';
                        $record->pivot->save();

                        Notification::make()
                            ->title('Meeting Declined')
                            ->success()
                            ->send();
                    }),

            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
