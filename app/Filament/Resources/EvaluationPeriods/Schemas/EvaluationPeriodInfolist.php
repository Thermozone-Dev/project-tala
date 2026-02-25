<?php

namespace App\Filament\Resources\EvaluationPeriods\Schemas;

use App\Models\EvaluationPeriod;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EvaluationPeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextEntry::make('date_from')
                    ->date(),
                TextEntry::make('date_to')
                    ->date(),
                TextEntry::make('status.name')
                    ->label('Status'),
                TextEntry::make('creator.email')
                    ->numeric(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (EvaluationPeriod $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
