<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Override;

class ListEvaluationRecords extends ListRecords
{

    protected static string $resource = EvaluationPeriodResource::class;

    public $record,$evaluator_id;

    protected static ?string $title = 'Evaluation Records';


    public function getBreadcrumbs(): array
    {
        $evaluator = User::find($this->evaluator_id);
        $evaluation = EvaluationPeriod::find($this->record);
        $evaluation_period = Carbon::parse($evaluation->date_from)->format('M d Y').' - '.Carbon::parse($evaluation->date_to)->format('M d Y');
        $array = [
            EvaluationPeriodResource::getUrl('index') => 'Evaluation Periods',
            EvaluationPeriodResource::getUrl('view', ['record' => $evaluation->id]) => $evaluation_period,
            '0' => $evaluator->getFullNameAttribute()
        ];
        return $array;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.title')
                    ->sortable('evaluation_forms.id')
                    ->wrap(),
                TextColumn::make('member.full_name')->label('Evaluated')
                    ->wrap(),
                TextColumn::make('committee.name')->badge()->wrap(),
                TextColumn::make('eval_status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'In Progress' => 'primary',
                        'Pending' => 'warning',
                        'Submitted' => 'success',
                        'For Review' => 'info',
                        'Reviewed' => 'info',
                    }),
            ])->recordActions([
                Action::make('View Evaluation')
                    ->url( function($record){
                        return $this->getResource()::getUrl('view-evaluation',['evaluation_id' => $record->evaluation_id, 'record_id' => $record->id]);
                    })
                    ->icon(Heroicon::Eye)
                    ->openUrlInNewTab(),

                Action::make('Print')
                    ->color('secondary')
                    ->url(fn($record) => route('queues-call-next', ['trustee_evaluation_id' => $record->id]))

                    ->icon(Heroicon::OutlinedPrinter)
                    ->openUrlInNewTab(),
                DeleteAction::make()->authorize(fn () => auth()->user()->can('delete')),
            ])
            ->recordUrl(function($record){
                return $this->getResource()::getUrl('view-evaluation',['evaluation_id' => $record->evaluation_id, 'record_id' => $record->id]);
            })
            ->defaultPaginationPageOption(50);
    }


    protected function getTableQuery(): Builder|Relation|null
    {
        return TrusteeHasEvaluation::query()
            ->where('evaluation_id', $this->record)
            ->where('evaluator_id', $this->evaluator_id)
            ->join('evaluation_forms', 'trustee_has_evaluation.ef_id', '=', 'evaluation_forms.id')
            ->orderBy('evaluation_forms.id', 'asc')
            ->select('trustee_has_evaluation.*');
    }



}
