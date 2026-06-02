<?php

namespace App\Livewire;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
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

        // If current user is a trustee, filter to show only their evaluations
        if (auth()->user()->hasRole('trustee')) {
            Notification::make()
                ->title('Lists Shortlisted')
                ->success()
                ->send();

            $query->where('id', auth()->user()->id);
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
                    ->formatStateUsing(fn ($_, $record) => $record->getFullNameAttribute())
                    ->sortable(false),

                TextColumn::make('pending_count')
                    ->label('Pending')
                    ->sortable(false),
                TextColumn::make('submitted_count')
                    ->label('Submitted')
                    ->sortable(false),
                TextColumn::make('total_count')
                    ->label('Evaluation Count')
                    ->sortable(false),

                TextColumn::make('submitted_count')
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
            ->recordActions([
                Action::make('view_evaluation')
                    ->label('View Evaluations')
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
