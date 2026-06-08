<?php

namespace App\Filament\Resources\Committees\Pages;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class EvaluationPeriods extends ListRecords
{
    protected static string $resource = CommitteeResource::class;

    public $record,$evaluator_id;
    protected $queryString = ['record','evaluator_id','evaluation_id'];
    protected static ?string $title = 'Evaluation Period';

    public function getBreadcrumbs(): array
    {
        $evaluator = User::find($this->evaluator_id);
        $committee = Committee::find($this->record);

        $array = [
            $this->getResourceUrl().'/'.$this->record => $committee->name,
            0 => $evaluator->fullname,
        ];
        return $array;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected function getTableQuery(): Builder|Relation|null
    {
        return EvaluationPeriod::query()
            ->whereHas('assignments', function ($query) {
                $query->where('evaluator_id', $this->evaluator_id);
            })->with('assignments')
            ->latest();
    }


    public function table(Table $table): Table
    {
        $query = TrusteeHasEvaluation::query()
            ->whereIn('trustee_evaluation_statuses_id',[1,3]) // 1 = In Progress and 3 = Pending
            ->whereHas('evaluationPeriod', fn($q) => $q->where('status_id', 1)) // 1 = Active
            ->where('committee_id', $this->record)
            ->where('evaluator_id', $this->evaluator_id);

        return $table
            ->columns([
                TextColumn::make('status.name'),
                TextColumn::make('date_from')->dateTime(),
                TextColumn::make('date_to')->dateTime(),
                TextColumn::make('id')
                    ->label('')
                    ->badge(fn(string $state) => (bool) (clone $query)->where('evaluation_id', $state)->count())
                    ->color('warning')
                    ->formatStateUsing(function (string $state) use ($query) {
                        return (clone $query)->where('evaluation_id', $state)->count() ?: null;
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                ViewAction::make()
                    ->authorize(check_committee_permission($this->record,'View:Committee'))
                    ->url(fn(Model $record): string => CommitteeResource::getUrl('evaluation-members', ['record' => $this->record,'evaluator_id' => $this->evaluator_id,'evaluation_id' => $record->id])),
            ])
            ->toolbarActions([
//                BulkActionGroup::make([
//                    DetachBulkAction::make(),
//                    DeleteBulkAction::make(),
//                ]),
            ]);
    }
}
