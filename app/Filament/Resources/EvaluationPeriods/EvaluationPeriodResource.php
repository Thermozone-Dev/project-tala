<?php

namespace App\Filament\Resources\EvaluationPeriods;

use App\Filament\Resources\EvaluationPeriods\Pages\CreateEvaluationPeriod;
use App\Filament\Resources\EvaluationPeriods\Pages\EditEvaluationPeriod;
use App\Filament\Resources\EvaluationPeriods\Pages\ListEvaluationPeriods;
use App\Filament\Resources\EvaluationPeriods\Pages\ListEvaluationRecords;
use App\Filament\Resources\EvaluationPeriods\Pages\ViewEvaluation;
use App\Filament\Resources\EvaluationPeriods\Pages\ViewEvaluationPeriod;
use App\Filament\Resources\EvaluationPeriods\Schemas\EvaluationPeriodForm;
use App\Filament\Resources\EvaluationPeriods\Schemas\EvaluationPeriodInfolist;
use App\Filament\Resources\EvaluationPeriods\Tables\EvaluationPeriodsTable;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

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
            'view-evaluation' => ViewEvaluation::route('/{evaluation_id}/form/{record_id}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        // Check if there's an active evaluation period
        $activeEvaluationPeriod = EvaluationPeriod::where('status_id', 1)->first();

        if (!$activeEvaluationPeriod) {
            return null;
        }

        $user = Auth::user();
        $userRole = $user->roles->first()?->name;
        $isExecutive = get_executive_role($userRole);

        // Status IDs: 1 = In Progress, 3 = Pending
        $pendingStatuses = [1, 3];

        if ($isExecutive) {
            // Executives: check if any evaluator has pending or in progress evaluations
            $hasPending = TrusteeHasEvaluation::where('evaluation_id', $activeEvaluationPeriod->id)
                ->whereIn('trustee_evaluation_statuses_id', $pendingStatuses)
                ->exists();
        } else {
            // Non-executives: check if authenticated user has pending or in progress evaluations
            $hasPending = TrusteeHasEvaluation::where('evaluation_id', $activeEvaluationPeriod->id)
                ->where('evaluator_id', $user->id)
                ->whereIn('trustee_evaluation_statuses_id', $pendingStatuses)
                ->exists();
        }

        return $hasPending ? '!' : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() ? 'danger' : null;
    }
}
