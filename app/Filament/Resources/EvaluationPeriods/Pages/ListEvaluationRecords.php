<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

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
            $this->getResourceUrl('index') => 'Evaluation Periods',
            $this->getResourceUrl('view',['record' => $evaluation->id]),
            '0' => $evaluation_period,
            '1' => $evaluator->getFullNameAttribute()

        ];
        return $array;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.shortcode'),
                TextColumn::make('evaluator.name')->formatStateUsing( function($record, $state){
                    return $state. ' '.$record->evaluator?->suffix ?? null;
                }),
                TextColumn::make('member.name')->label('Evaluated'),

                TextColumn::make('committee.name')->badge(),
                TextColumn::make('eval_status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'primary',
                        'Pending' => 'warning',
                        'Locked' => 'success',
                        'For Review' => 'info',
                        'Reviewed' => 'info',

                    }),
            ])->recordActions([
                Action::make('View Evaluation')
                    ->url( function($record){
                        return CommitteeResource::getUrl('view-evaluation',['record' => $record->committee_id, 'record_id' => $record->id]);
                    })
                    ->icon(Heroicon::Eye)
                    ->openUrlInNewTab(),
                Action::make('Commitee Evaluation')
                    ->url( function($record){
                        return CommitteeResource::getUrl('evaluation-members',['record' => $record->committee_id, 'evaluator_id' => $record->evaluator_id, 'evaluation_id' => $record->evaluation_id]);
                    })
                    ->color('warning')
                    ->icon(Heroicon::UserGroup)
                    ->openUrlInNewTab(),
                Action::make('Print')
                    ->color('secondary')
                    ->url(fn($record) => route('queues-call-next', ['trustee_evaluation_id' => $record->id]))

                    ->icon(Heroicon::OutlinedPrinter)
                    ->openUrlInNewTab(),
            ]);
    }
    protected function getTableQuery(): Builder|Relation|null
    {
        return TrusteeHasEvaluation::query()->where('evaluation_id', $this->record)->where('evaluator_id', $this->evaluator_id);
    }



}
