<?php

namespace App\Filament\Resources\EvaluationPeriods\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EvaluationPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_from')
                    ->default('06/25/2024')
                    ->required(),
                DatePicker::make('date_to')
                    ->default('06/25/2026')
                    ->required(),
                // Selec::make('status_id')
                //     ->required()
                //     ->numeric(),
                // TextInput::make('created_by')
                //     ->required()
                //     ->numeric(),
            ]);
    }
}
