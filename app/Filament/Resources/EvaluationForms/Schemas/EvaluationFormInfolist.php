<?php

namespace App\Filament\Resources\EvaluationForms\Schemas;

use App\Models\EvaluationForm;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class EvaluationFormInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evaluation Form')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                TextEntry::make('title')
                                    ->columnSpan(3),

                                TextEntry::make('shortcode')
                                    ->columnSpan(1),

                                TextEntry::make('pdfTemplate.name')
                                    ->label('PDF Template')
                                    ->columnSpan(1),
                            ]),
                    ]),

                RepeatableEntry::make('sections')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->label('Sections')
                    ->schema([
                        Section::make(fn ($state) => $state['title'] ?? '')
                            ->hiddenLabel()
                            ->collapsible()
                            ->schema([
                                TextEntry::make('title')
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Bold)
                                    ->label('Title'),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('sectionType.name')
                                            ->label('Type'),

                                        TextEntry::make('ratingScale.name')
                                            ->label('Rating Scale'),

                                        IconEntry::make('add_remarks')
                                            ->label('Include Remarks')
                                            ->boolean(),
                                    ]),

                                Section::make('Questionnaires')
                                    ->visible(fn ($record) => $record?->section_type_id == 1)
                                    ->schema([
                                        RepeatableEntry::make('questionnaires')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->hiddenLabel(),
                                            ]),
                                    ]),

                                Section::make('Attendance Matrix')
                                    ->collapsible()
                                    ->visible(false)
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                IconEntry::make('attendanceSection.show_total_meetings')
                                                    ->label('Total Meetings')
                                                    ->boolean(),

                                                IconEntry::make('attendanceSection.show_physically_present')
                                                    ->label('Physically Present')
                                                    ->boolean(),

                                                IconEntry::make('attendanceSection.show_considered_present')
                                                    ->label('Considered Present')
                                                    ->boolean(),

                                                IconEntry::make('attendanceSection.show_total_present')
                                                    ->label('Total Present')
                                                    ->boolean(),

                                                IconEntry::make('attendanceSection.show_attendance_rating')
                                                    ->label('Attendance Rating')
                                                    ->boolean(),
                                            ]),

                                        RepeatableEntry::make('attendanceSection.meetings')
                                            ->label('Meetings')
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Meeting Name'),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
