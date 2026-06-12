<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Models\Committee;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Meeting Details')
                    ->columnSpanFull()
                    ->columns()
                    ->schema([
                        TextInput::make('title')->required(),

                        TextInput::make('meeting_link')->required(),

                        Select::make('meeting_type_id')
                            ->relationship('meetingType', 'name')
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                ColorPicker::make('color')->required(),
                            ])
                            ->createOptionAction(fn ($action) => $action->modalHeading('Create Meeting Type')),

                        DateTimePicker::make('scheduled_at')->required()
                            ->default(fn () => now()->setTime(8, 0)),
                    ]),

                Section::make('Attendees')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('committee_id')
                            ->label('Committee')
                            ->required()
                            ->columns(4)
                            ->reactive()
                            ->options(function (){
                                return Committee::pluck('name', 'id')->toArray();
                            })
                            ->afterStateUpdated(fn(Set $set) => $set('attendees', null)),
                        CheckboxList::make('attendees')
                            ->hiddenLabel()
                            ->bulkToggleable()
                            ->reactive()
                            ->options(function (Get $get){
                                if($get('committee_id')){
                                    return User::whereHas('committees', fn($q) => $q->where('committee_id', $get('committee_id')))->pluck('name', 'id')->toArray();
                                }
                                return [];
                            }),
                        Select::make('attendees')
                            ->required()
                            ->disabled()
                            ->reactive()
                            ->multiple()
                            ->searchable()
                            ->options(function (Get $get){
                                if($get('committee_id')){
                                    return User::whereHas('committees', fn($q) => $q->where('committee_id', $get('committee_id')))->pluck('name', 'id')->toArray();
                                }
                                return [];
                            })
                    ])
            ]);
    }
}
