<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Filament\Widgets\EvaluationStatsOverview;
use App\Filament\Widgets\EvaluationStatusChart;
use App\Filament\Widgets\EvaluatorsPerformanceChart;
use App\Filament\Widgets\EvaluationFormBreakdown;
use App\Livewire\AttendanceEvaluation;
use App\Livewire\EvaluationListTable;
use App\Livewire\EvaluationPeriodOverview;
use App\Models\Trustee;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEvaluationPeriod extends ViewRecord
{
    protected static string $resource = EvaluationPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $trustee_id = Trustee::where('user_id', auth()->user()->id)?->first()?->user_id ?? null;

        return [
            EvaluationStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AttendanceEvaluation::make(['evaluation_period_id' => $this->record->id]),
            EvaluationListTable::make(['evaluation_period_id' => $this->record->id]),
        ];
    }
}
