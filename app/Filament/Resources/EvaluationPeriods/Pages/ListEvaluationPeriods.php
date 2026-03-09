<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvaluationPeriods extends ListRecords
{
    protected static string $resource = EvaluationPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Start and Evaluation Period'),
        ];
    }
}
