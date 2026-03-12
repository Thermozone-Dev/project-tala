<?php

namespace App\Filament\Resources\EvaluationPeriods;

use App\Filament\Resources\EvaluationPeriods\Pages\CreateEvaluationPeriod;
use App\Filament\Resources\EvaluationPeriods\Pages\EditEvaluationPeriod;
use App\Filament\Resources\EvaluationPeriods\Pages\ListEvaluationPeriods;
use App\Filament\Resources\EvaluationPeriods\Pages\ListEvaluationRecords;
use App\Filament\Resources\EvaluationPeriods\Pages\ViewEvaluationPeriod;
use App\Filament\Resources\EvaluationPeriods\Schemas\EvaluationPeriodForm;
use App\Filament\Resources\EvaluationPeriods\Schemas\EvaluationPeriodInfolist;
use App\Filament\Resources\EvaluationPeriods\Tables\EvaluationPeriodsTable;
use App\Models\EvaluationPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluationPeriodResource extends Resource
{
    protected static ?string $model = EvaluationPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function form(Schema $schema): Schema
    {
        return EvaluationPeriodForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EvaluationPeriodInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EvaluationPeriodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvaluationPeriods::route('/'),
            'create' => CreateEvaluationPeriod::route('/create'),
            'view' => ViewEvaluationPeriod::route('/{record}'),
            'edit' => EditEvaluationPeriod::route('/{record}/edit'),
            'evaluation-trustee' => ListEvaluationRecords::route('/{record}/evaluator/{evaluator_id}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
