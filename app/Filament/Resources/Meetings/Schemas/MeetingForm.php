<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Models\Committee;
use App\Models\Meeting;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
//                TextInput::make('title')
//                    ->required(),
//                TextInput::make('meet_url')
//                    ->label('Google Meet URL')
//                    ->url()
//                    ->required()
//                    ->placeholder('https://meet.google.com/xxx-xxxx-xxx'),
//                Textarea::make('description')
//                    ->rows(3)
//                    ->columnSpanFull(),
//                DateTimePicker::make('scheduled_at')
//                    ->required(),
//                TextInput::make('duration_minutes')
//                    ->required()
//                    ->numeric()
//                    ->default(60),
//                TextInput::make('meeting_status_id')
//                    ->required()
//                    ->numeric()
//                    ->default(1),
//                TextInput::make('created_by')
//                    ->required()
//                    ->numeric(),
                Section::make('Meeting Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('meeting_url')
                            ->label('Meeting URL')
                            ->url()
                            ->required()
                            ->placeholder('https://meet.google.com/xxx-xxxx-xxx'),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Schedule')
                    ->schema([
                        DateTimePicker::make('scheduled_at')
                            ->label('Date & Time')
                            ->default(now()->startOfDay()->addHours(8))
                            ->displayFormat('d/m/Y H:i')
                            ->required()
                            ->minDate(now()),
                        TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->default(60)
                            ->minValue(15)
                            ->suffix('min'),

                        Select::make('meeting_status_id')
                            ->relationship('status','label')
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Attendees')
                    ->columnSpanFull()
                    ->schema([
                        Checkbox::make('search_by_committee')->reactive(),
                        CheckboxList::make('committees')
                            ->bulkToggleable()
                            ->reactive()
                            ->visible(fn(Get $get) => $get('search_by_committee') == 1)
                            ->options(function (){
                                return Committee::pluck('name', 'id')->toArray();
                            }),
                        CheckboxList::make('attendees')
                            ->bulkToggleable()
                            ->reactive()
                            ->options(function (Get $get){
                                if($get('committees')){
                                    return User::whereHas('committee_has_trustees', fn($q) => $q->whereIn('committee_id', $get('committees')))->pluck('name', 'id')->toArray();
                                }
                                return [];
                            }),
                        Select::make('attendees')
                            ->reactive()
                            ->multiple()
                            ->searchable()
                            ->options(function (Get $get){
                                if($get('committees')){
                                    return User::whereHas('committee_has_trustees', fn($q) => $q->whereIn('committee_id', $get('committees')))->pluck('name', 'id')->toArray();
                                }
                                return [];
                            })
                    ])
            ]);
    }
}
