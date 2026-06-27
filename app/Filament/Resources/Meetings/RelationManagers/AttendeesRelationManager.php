<?php

namespace App\Filament\Resources\Meetings\RelationManagers;

use App\Models\MeetingAttendanceStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;


class AttendeesRelationManager extends RelationManager
{
    #[On('refreshAttendees')]
    public function refresh(): void
    {}
    protected static string $relationship = 'attendees';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user')
            ->columns([
                TextColumn::make('user.full_name')
                    ->sortable([
                        'last_name'
                    ])
                    ->searchable([
                        'last_name',
                        'first_name',
                        'middle_name'
                    ])
                    ->label('Name'),
                TextColumn::make('attendanceStatus.name')
                    ->sortable()
                    ->label('Status'),
                TextColumn::make('is_late')
                    ->label('Is Late?')
                    ->formatStateUsing(fn ($state) => ($state == true) ? 'Late' : 'Not Late')
                    ->badge()
                    ->color(fn ($state) => ($state == true) ? 'danger' : 'success'),
                TextColumn::make('mark_timestamp')
                    ->label('Marked At')
                    ->formatStateUsing(fn ($state) => $state->format('M d, Y H:i A')),
                TextColumn::make('updatedBy.name')
                    ->label('Updated By'),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                Action::make('Update')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->fillForm(fn ($record) => [
                        'mark_timestamp' => $record->mark_timestamp ?? now(),
                        'attendance_status_id' => $record->attendance_status_id,
                        'is_late' => $record->is_late,
                    ])
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('mark_timestamp')
                                    ->required()
                                    ->label('Marked At')
                                    ->default(now()->format('M d, Y H:i A'))
                                    ->seconds(false),
                                Select::make('attendance_status_id')
                                    ->required()
                                    ->label('Status')
                                    ->options(MeetingAttendanceStatus::pluck('name', 'id')),
                                Select::make('is_late')
                                    ->required()
                                    ->label('Is Late?')
                                    ->options([
                                        0 => 'Not Late',
                                        1 => 'Late',
                                    ]),
                            ])
                    ])
                    ->action(function ($record,$data){
                        $data['updated_by'] = Auth::user()->id;

                        $record->update($data);
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
