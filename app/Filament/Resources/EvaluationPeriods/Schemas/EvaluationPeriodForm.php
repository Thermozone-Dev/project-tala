<?php

namespace App\Filament\Resources\EvaluationPeriods\Schemas;

use App\Models\EvaluationPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EvaluationPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_from')
                    ->minDate(fn () => EvaluationPeriod::orderBy('date_to', 'desc')->value('date_to') ?? null)
                    ->required(),
                DatePicker::make('date_to')
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
                            ->relationship(
                                name: 'corporateSecretarySign',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', fn($q) =>
                                    $q->whereIn('name', ['Corporate Secretary'])
                                ),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->full_name)
                            ->columnSpan(1)
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('secretariat')
                            ->relationship(
                                name: 'secretariatUser',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', fn($q) =>
                                    $q->whereIn('name', ['secretariat'])
                                ),
                            )
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->full_name)
                            ->label('Head, Office Board of the Secretariat')
                            ->columnSpan(1)
                            ->searchable()
                            ->preload(),
                    ])

            ]);
    }
}
