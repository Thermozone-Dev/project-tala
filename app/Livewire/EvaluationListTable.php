<?php

namespace App\Livewire;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\Trustee;
use App\Models\TrusteeHasEvaluation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EvaluationListTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public $evaluation_period_id;

    protected function getTableHeading(): string|Htmlable|null
    {
        return 'Trustee Evaluator(s) Lists';
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'user_id';
    }




    public function table(Table $table): Table
    {
        $test = TrusteeHasEvaluation::query()
            ->where('evaluation_id', $this->evaluation_period_id)
            ->pluck('evaluator_id')->toArray();

        if(Trustee::where('user_id', auth()->user()->id)->first()){

            Notification::make()
                ->title('Lists Shortlisted')
                ->success()
                ->send();

            $test = [auth()->user()->id];

        }

        $query = Trustee::query()
        ->whereIn('user_id', array_unique($test))
        ->select(['user_id','trustees.id' ,'user_id as evaluator_id'])

        ->withCount([
            'active_evaluation as pending_count' => function (Builder $query) {
                $query->where('evaluation_id', $this->evaluation_period_id)
                        ->where('trustee_evaluation_statuses_id', 1);
            },

            'active_evaluation as submitted_count' => function (Builder $query) {
                $query->where('evaluation_id', $this->evaluation_period_id)
                        ->where('trustee_evaluation_statuses_id', 2);
            },

            'active_evaluation as total_count' => function (Builder $query) {
                $query->where('evaluation_id', $this->evaluation_period_id);
            },
        ])
        ->selectRaw('
            CASE
                WHEN (
                    SELECT COUNT(*)
                    FROM trustee_has_evaluation
                    WHERE evaluation_id = ?
                    AND evaluator_id = trustees.user_id
                ) > 0
                THEN ROUND(
                    (
                        SELECT COUNT(*)
                        FROM trustee_has_evaluation
                        WHERE evaluation_id = ?
                        AND evaluator_id = trustees.user_id
                        AND trustee_evaluation_statuses_id = 2
                    ) * 100.0 /
                    (
                        SELECT COUNT(*)
                        FROM trustee_has_evaluation
                        WHERE evaluation_id = ?
                        AND evaluator_id = trustees.user_id
                    ),
                1)
                ELSE 0
            END as submitted_percentage
        ', [
            $this->evaluation_period_id,
            $this->evaluation_period_id,
            $this->evaluation_period_id
        ]);

        return $table
            ->query(fn (): Builder => $query)
            ->columns([
                TextColumn::make('user_id')
                    ->label('Trustee Name')
                    ->formatStateUsing(fn ($state) => Trustee::where('user_id', $state)->first()?->user?->getFullNameAttribute() ?? 'Unknown')
                    ->sortable(),

                TextColumn::make('pending_count')
                    ->label('Pending')
                    ->sortable(),
                TextColumn::make('submitted_count')
                    ->label('Submitted')
                    ->sortable(),
                TextColumn::make('total_count')
                    ->label('Evaluation Count')

                    ->sortable(),


                TextColumn::make('submitted_percentage')
                    ->label('Completion %')
                    ->formatStateUsing(fn ($state) => $state . ' %')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('view_evaluation')
                    ->label('View Evaluations')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => EvaluationPeriodResource::getUrl('evaluation-trustee',['record' => $record->active_evaluation->sortByDesc('id')->first()->evaluation_id, 'evaluator_id' => $record->user_id]) ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
