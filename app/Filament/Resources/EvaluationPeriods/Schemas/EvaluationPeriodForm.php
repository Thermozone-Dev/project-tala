<?php

namespace App\Filament\Resources\EvaluationPeriods\Schemas;

use App\Models\EvaluationPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EvaluationPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_from')
                    ->default('06/25/2024')
                    ->minDate(fn () => EvaluationPeriod::orderBy('date_to', 'desc')->value('date_to') ?? null)
                    ->required(),
                DatePicker::make('date_to')
                    ->default('06/25/2026')
                    ->minDate( fn (Get $get) => $get('date_from') )
                    ->required(),
                Select::make('status_id')
                    ->relationship(name: 'status', titleAttribute: 'name')
                    ->hiddenOn('create')
                    ->required(),
                Fieldset::make('Signatories')
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        Select::make('corporate_secretary_sign')
                            ->label('Corporate Secretary')
                            ->columnSpan(1)
                            ->options(function () {
                                return \App\Models\User::whereHas('roles', fn($q) =>
                                    $q->where('name', 'Corporate Secretary')
                                )->get()->mapWithKeys(fn($user) => [
                                    $user->id => $user->full_name
                                ]);
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('secretariat')
                            ->label('Head, Office Board of the Secretariat')
                            ->columnSpan(1)
                            ->required()
                            ->options(function () {
                                return \App\Models\User::whereHas('roles', fn($q) =>
                                    $q->where('name', 'secretariat')
                                )
                                ->whereHas('roles', fn($q) => $q->whereIn('name', ['Corporate Secretary','secretariat']))
                                ->get()->mapWithKeys(fn($user) => [
                                    $user->id => $user->full_name
                                ]);
                            })
                            ->searchable()
                            ->preload(),
                    ])

            ]);
    }
}
