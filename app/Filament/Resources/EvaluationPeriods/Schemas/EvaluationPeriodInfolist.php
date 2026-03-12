<?php

namespace App\Filament\Resources\EvaluationPeriods\Schemas;

use App\Models\EvaluationPeriod;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class EvaluationPeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Fieldset::make('Evaluation Period Details')->columns(3)->schema([
                    TextEntry::make('date_from')
                        ->date(),
                    TextEntry::make('date_to')
                        ->date(),
                    TextEntry::make('status.name')
                        ->badge()
                        ->color(fn (EvaluationPeriod $record) => match ($record->status_id) {
                            1 => 'primary',
                            2 => 'warning',
                            4 => 'danger',
                            3 => 'success',
                            default => 'primary',
                        })
                        ->label('Status'),
                    TextEntry::make('creator.id')
                        ->label('Created By')
                        ->state(fn (Model $record) => $record?->creator?->getFullNameAttribute() ?? null),
                    TextEntry::make('created_at')
                        ->dateTime('M j, Y H:i A')
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->dateTime()
                        ->dateTime('M j, Y H:i A')
                        ->placeholder('-'),
                ])

            ]);
    }
}
