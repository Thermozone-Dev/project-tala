<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\EvaluationPeriod;
use App\Models\EvaluationForm;
use App\Models\Committee;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
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
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_evaluation_form')
                ->label('Add Evaluation')
                ->visible(fn () => auth()->user()->can('Update:EvaluationPeriod'))
                ->schema([
                    Placeholder::make('delegation_note')
                        ->content('⚠️ **Note:** Ensure the selected evaluator has the appropriate role for the selected form. Manual delegations will appear in reports and evaluator records.'),

                    Select::make('ef_id')
                        ->label('Form Type')
                        ->options(function () {
                            // Exclude self-assessment forms (Form 8, 9)
                            return EvaluationForm::whereNotIn('id', [8, 9])
                                ->pluck('title', 'id')
                                ->toArray();
                        })
                        ->required(),

                    Select::make('member_id')
                        ->label('Evaluatee / Member')
                        ->searchable()
                        ->preload()
                        ->options(function (Get $get) {
                            $form_id = $get('ef_id');

                            // For self-assessment forms (8, 9), member_id is not needed
                            if (in_array($form_id, [8, 9])) {
                                return [];
                            }

                            // Get all users except executives
                            $executive_roles = ['super admin', 'secretariat'];
                            return User::whereHas('roles', function ($query) use ($executive_roles) {
                                $query->whereNotIn('name', $executive_roles);
                            })
                            ->orWhereDoesntHave('roles')
                            ->get()
                            ->mapWithKeys(fn($user) => [$user->id => $user->full_name])
                            ->toArray();
                        })
                        ->required(fn (Get $get) => !in_array($get('ef_id'), [8, 9]))
                        ->reactive(),

                    Select::make('committee_id')
                        ->label('Committee')
                        ->options(function () {
                            return Committee::pluck('name', 'id')->toArray();
                        })
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $evaluationPeriod = EvaluationPeriod::find($this->record);
                    $evaluator_id = $this->evaluator_id;
                    $form_id = (int) $data['ef_id'];
                    $member_id = $data['member_id'] ? (int) $data['member_id'] : null;
                    $committee_id = $data['committee_id'] ? (int) $data['committee_id'] : null;

                    // Check for duplicates in TrusteeHasEvaluation
                    $existingEvaluation = TrusteeHasEvaluation::where('evaluation_id', $evaluationPeriod->id)
                        ->where('ef_id', $form_id)
                        ->where('evaluator_id', $evaluator_id)
                        ->where('member_id', $member_id)
                        ->where('committee_id', $committee_id)
                        ->exists();

                    if ($existingEvaluation) {
                        Notification::make()
                            ->title('Duplicate Entry')
                            ->danger()
                            ->body('This evaluation form delegation already exists for this evaluator.')
                            ->send();
                        return;
                    }

                    // Save to TrusteeHasEvaluation
                    TrusteeHasEvaluation::create([
                        'evaluation_id' => $evaluationPeriod->id,
                        'ef_id' => $form_id,
                        'evaluator_id' => $evaluator_id,
                        'member_id' => $member_id,
                        'committee_id' => $committee_id,
                        'trustee_evaluation_statuses_id' => 3, // Pending status
                    ]);

                    Notification::make()
                        ->title('Evaluation Added')
                        ->success()
                        ->body('Evaluation form has been successfully delegated.')
                        ->send();

                    // Refresh the table
                    $this->resetTable();
                }),
        ];
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
                DeleteAction::make()
                    ->authorize(fn () => auth()->user()->can('Update:EvaluationPeriod')),

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
