<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\EvaluationPeriod;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

class ListEvaluationPeriods extends ListRecords
{
    protected static string $resource = EvaluationPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('startEvaluation')
                ->label('Start Evaluation Period')
                ->visible(!EvaluationPeriod::where('status_id', 1)->exists())
                ->icon('heroicon-o-plus')
                ->action(function () {
                    return redirect(
                        EvaluationPeriodResource::getUrl('create')
                    );
                }),

            Action::make('evaluation-warning-modal')
                ->label('Start Evaluation Period')
                ->modalHeading('Start Evaluation Period')
                ->icon('heroicon-o-plus')
                ->modalContent(view('filament.modals.evaluation-warning'))
                ->visible(EvaluationPeriod::where('status_id', 1)->exists())
                ->form([
                    TextInput::make('confirmation')
                        ->hiddenLabel('Type CONFIRM to proceed')
                        ->required()
                        ->rule('in:CONFIRM')
                ])
                ->action(function () {
                    return redirect(
                        EvaluationPeriodResource::getUrl('create')
                    );
                })
        ];
    }
}
