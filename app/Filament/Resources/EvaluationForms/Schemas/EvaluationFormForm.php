<?php

namespace App\Filament\Resources\EvaluationForms\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EvaluationFormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(5)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->columnSpan(3)
                            ->required(),
                        TextInput::make('shortcode')
                            ->columnSpan(1)
                            ->required(),

                        Select::make('pdf_template_id')
                            ->label('PDF Template')
                            ->columnSpan(1)
                            ->relationship('pdfTemplate', 'name')
                            ->required(),

                    ]),


                Repeater::make('sections')
                    ->columnSpanFull()
                    ->collapsible()
                    ->itemLabel(fn ($state) =>  isset($state['title']) ? $state['title'] : null )
                    ->collapsed()
                    ->relationship()
                    ->schema([

                        TextInput::make('title')
                            ->columnSpanFull()
                            ->required(),

                        Grid::make(3)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('rating_scale_id')
                                    ->relationship('ratingScale', 'type')
                                    ->required(),

                                Select::make('section_type_id')
                                    ->relationship('sectionType', 'name')
                                    ->live(true)
                                    ->required(),

                                Checkbox::make('add_remarks')
                                    ->label('Include remarks')
                                    ->hint('for questionaire sections only *')
                                    ->hintColor('danger')
                                    ->default(false),
                            ]),

                        Section::make('Questionnaire Lists')
                            ->columnSpanFull()
                            ->collapsible()
                            ->visible(fn ($get) => $get('section_type_id') == 1)
                            ->schema([
                                Repeater::make('questionnaires')
                                    ->visible(fn ($get) => $get('section_type_id') == 1)
                                    ->columnSpanFull()
                                    ->grid(2)
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Question')
                                            ->columnSpan(1)
                                            ->required()
                                    ]),
                            ]),


                        Section::make('Attendance Matrix')
                            ->visible(fn ($get) => $get('section_type_id') == 2)
                            ->relationship('attendanceSection')
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                Fieldset::make('Attendance Criteria')
                                    ->columnSpanFull()
                                    ->columns(5)
                                    ->schema([
                                        Checkbox::make('show_total_meetings')
                                            ->label('Show Total Meetings')
                                            ->default(true),
                                        Checkbox::make('show_physically_present')
                                            ->label('Show Physically Present ')
                                            ->default(true),
                                        Checkbox::make('show_considered_present')
                                            ->label('Show Considered as present')
                                            ->default(true),
                                        Checkbox::make('show_total_present')
                                            ->label('Show Total Meetings Present')
                                            ->default(true),
                                        Checkbox::make('show_attendance_rating')
                                            ->label('Show Attendance Rating')
                                            ->default(true),
                                    ]),

                                    Fieldset::make('Meetings')
                                        ->columnSpanFull()
                                        ->schema([
                                            Repeater::make('meetings')
                                                ->hiddenLabel()
                                                ->columnSpanFull()
                                                ->relationship()
                                                ->grid(3)
                                                ->schema([

                                                    TextInput::make('name')
                                                        ->label('Attendance meeting name')
                                                        ->columnSpan(1)
                                                        ->required()
                                                ]),
                                            ]),

                            ])

                    ])
                    ->columns(2)
            ]);
    }
}
