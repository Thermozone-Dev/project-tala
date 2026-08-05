<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\MeetingType;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
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
                        Select::make('evaluation_period_id')
                            ->relationship(
                                name: 'evaluationPeriod',
                                modifyQueryUsing: fn ($query) => $query->orderBy('date_from', 'desc')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (EvaluationPeriod $record) => $record->formatted_coverage
                            )
                            ->preload()
                            ->default(fn () => EvaluationPeriod::active()->value('id'))
                            ->searchable(),

                        Group::make([
                            TextInput::make('title')->required(),

                            Select::make('meeting_type_id')
                                ->relationship('meetingType', 'name')
                                ->required()
                                ->reactive()
                                ->default(1)
                                ->disableOptionWhen(fn (string $value): bool => $value != 1)
                        ])
                        ->columns()
                        ->columnSpanFull(),

                        Group::make([
                            TextInput::make('meeting_link')
                                ->required()
                                ->hintAction(function (Get $get){

                                    if(!$get('meeting_type_id')) return;

                                    $meeting_type = MeetingType::find($get('meeting_type_id'));

                                    $link = $meeting_type->url;

                                    return Action::make('create_meeting')
                                        ->label('Create Meeting Link')
                                        ->url($link, shouldOpenInNewTab: true);
                                })
                                ->helperText(function (Get $get) {
                                    return "Click 'Create Meeting Link' to generate a new meeting link, then paste the generated URL into this field.";
                                }),

                            DateTimePicker::make('scheduled_at')->required()
                                ->default(fn () => now()->setTime(8, 0)),
                        ])
                            ->columnSpanFull()
                            ->columns()
                        ->visible(fn(Get $get) => $get('meeting_type_id'))
                    ]),

                Section::make('Attendees')
                    ->columnSpanFull()
                    ->schema([
                            Select::make('committee_id')
                                ->label('Category')
                                ->required()
                                ->reactive()
                                ->options([
                                    'Committees' => Committee::pluck('name', 'id')->toArray(),
                                    '--------------------' => ['bot_meetings' => 'BOT Meetings',],
                                ])
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if($state == 'bot_meetings'){
                                        $set(
                                            'attendees',
                                            User::whereHas(
                                                'roles',
                                                fn ($q) => $q->whereIn('name',[
                                                    'Trustee',
                                                    'Corporate Secretary',
                                                    'Lead Resource Person',
                                                    'Treasurer',
                                                    'Comptroller',
                                                    'EVP-GM'
                                                ])
                                            )
                                                ->pluck('id')
                                                ->toArray()
                                        );
                                    }else{
                                        $set(
                                            'attendees',
                                            User::whereHas(
                                                'committees',
                                                fn ($q) => $q->where('committee_id', $state)
                                            )
                                                ->pluck('id')
                                                ->toArray()
                                        );
                                    }
                                }),
                            CheckboxList::make('attendees')
                                ->hiddenLabel()
                                ->columns(4)
                                ->bulkToggleable()
                                ->reactive()
                                ->options(function (Get $get) {
                                    if ($get('committee_id') && $get('committee_id') == 'bot_meetings') {
                                        return User::whereHas(
                                                'roles',
                                                fn ($q) => $q->whereIn('name',[
                                                    'Trustee',
                                                    'Corporate Secretary',
                                                    'Lead Resource Person',
                                                    'Treasurer',
                                                    'Comptroller',
                                                    'EVP-GM'
                                                ])
                                            )
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }else if ($get('committee_id')){
                                        return User::whereHas(
                                                'committees',
                                                fn ($q) => $q->where('committee_id', $get('committee_id'))
                                            )
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }

                                    return [];
                                }),
                    ])
            ]);
    }
}
