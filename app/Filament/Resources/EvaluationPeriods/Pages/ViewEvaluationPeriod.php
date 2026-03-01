<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
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
}
