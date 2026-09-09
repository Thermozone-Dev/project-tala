<?php

namespace App\Filament\Resources\Committees\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommitteeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        ColorPicker::make('color')->required(),

                        FileUpload::make('charter')
                            ->label('Committee Charter')
                            ->disk('public')
                            ->directory('committee-files/charters')
                            ->acceptedFileTypes([
                                'application/pdf',
                            ])
                            ->openable()
                            ->downloadable(),

                        FileUpload::make('agenda_forecast')
                            ->label('Agenda Forecast')
                            ->disk('public')
                            ->directory('committee-files/agenda-forecasts')
                            ->acceptedFileTypes([
                                'application/pdf',
                            ])
                            ->openable()
                            ->downloadable(),
                    ])

            ]);
    }
}
