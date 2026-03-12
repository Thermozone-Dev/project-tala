<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
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
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $trustee_id = Trustee::where('user_id', auth()->user()->id)?->first()?->user_id ?? null;

        return [
            EvaluationPeriodOverview::make(['evaluation_period_id' => $this->record->id,'trustee_id' => $trustee_id]),
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            EvaluationListTable::make(['evaluation_period_id' => $this->record->id]),
        ];
    }
}
