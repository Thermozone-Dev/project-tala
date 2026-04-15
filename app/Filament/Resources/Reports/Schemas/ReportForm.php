<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('generated_by')->default(auth()->id()),
                Section::make('Report Details')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('evaluation_period_id')
                            ->relationship('evaluationPeriod', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                \Carbon\Carbon::parse($record->date_from)->format('M d, Y') . ' - ' .
                                \Carbon\Carbon::parse($record->date_to)->format('M d, Y')
                            )
                            ->preload()
                            ->searchable()
                            ->required(),

                        Select::make('report_type_id')
                            ->relationship('reportType', 'name')
                            ->preload()
                            ->disableOptionWhen(fn (string $value): bool => $value != 1)
                            ->searchable()
                            ->required(),

                    ])->columns(2),
            ]);
    }
}
