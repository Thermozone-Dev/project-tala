<?php

namespace App\Livewire;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class EvaluationListTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public $evaluation_period_id;

    protected function getTableHeading(): string|Htmlable|null
    {
        return 'Trustee Evaluators';
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'id';
    }




    public function table(Table $table): Table
    {
        // Get board member roles for filtering
        $boardMemberRoles = get_board_members();

        // Build base query for board members
        $query = User::query()
            ->role($boardMemberRoles);

        // Filter by role: executives see all evaluators, non-executives see only themselves
        if (!get_executive_role(auth()->user()->roles->first()?->name)) {
            $query->where('id', auth()->id());
        }

        // Filter evaluations by current evaluation period
        $query->with([
            'evaluation' => function ($q) {
                $q->where('evaluation_id', $this->evaluation_period_id);
            }
        ]);


        // Add evaluation counts using the relationship
        $query->withCount([
            'evaluation as pending_count' => function (Builder $q) {
                $q->where('evaluation_id', $this->evaluation_period_id)
                    ->where('trustee_evaluation_statuses_id', 3);
            },
             'evaluation as in_progress' => function (Builder $q) {
                $q->where('evaluation_id', $this->evaluation_period_id)
                    ->where('trustee_evaluation_statuses_id', 1);
            },
            'evaluation as submitted_count' => function (Builder $q) {
                $q->where('evaluation_id', $this->evaluation_period_id)
                    ->where('trustee_evaluation_statuses_id', 2);
            },
            'evaluation as total_count' => function (Builder $q) {
                $q->where('evaluation_id', $this->evaluation_period_id);
            },
        ]);
        return $table
            ->query(fn (): Builder => $query)
            ->columns([
                TextColumn::make('first_name')
                    ->label('Trustee Name')
                    ->wrap()
                    ->formatStateUsing(fn ($_, $record) => $record->getFullNameAttribute())
                    ->sortable(),

                TextColumn::make('in_progress')
                    ->label('In Progress')
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

                TextColumn::make('total_count')
                    ->label('Completion %')
                    ->formatStateUsing(function ($_, $record) {
                        if ($record->total_count == 0) {
                            return '0 %';
                        }
                        $percentage = round(($record->submitted_count / $record->total_count) * 100, 1);
                        return $percentage . ' %';
                    })
                    ->sortable(false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordUrl(fn ($record) => EvaluationPeriodResource::getUrl('evaluation-trustee',['record' => $this->evaluation_period_id, 'evaluator_id' => $record->id]), true)
            ->recordActions([
                Action::make('view_evaluation')
                    ->label('Open Evaluations')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => EvaluationPeriodResource::getUrl('evaluation-trustee',['record' => $this->evaluation_period_id, 'evaluator_id' => $record->id]) ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
