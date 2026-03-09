<?php

namespace App\Filament\Resources\Committees\Pages;

use App\Actions\Form\AssessmentEvaluationFields;
use App\Actions\Form\AttendanceEvaluationFields;
use App\Actions\Form\OtherCommentsFields;
use App\Actions\SaveAssessmentEvaluation;
use App\Actions\SaveAttendanceEvaluation;
use App\Actions\SaveOtherComments;
use App\Filament\Resources\Committees\CommitteeResource;
use App\Models\AttendanceAnswer;
use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\OtherCommentAnswer;
use App\Models\QuestionaireAnswer;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity as ActivityLogModel;

class ViewCommitteeEvaluation extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    protected static string $resource = CommitteeResource::class;

    public $record,$record_id;
    protected $queryString = ['record','record_id'];
    protected string $view = 'filament.resources.committees.pages.view-committee-evaluation';

    public function getTitle(): string|Htmlable
    {
        $view_record = TrusteeHasEvaluation::find($this->record_id);
        return $view_record->form->title;
    }

    public function getBreadcrumbs(): array
    {
        $view_record = TrusteeHasEvaluation::find($this->record_id);

        $evaluator = User::find($view_record->evaluator_id);
        $committee = Committee::find($this->record);

        $evaluation = EvaluationPeriod::find($view_record->evaluation_id);
        $evaluation_period = Carbon::parse($evaluation->date_from)->format('M d Y').' - '.Carbon::parse($evaluation->date_to)->format('M d Y');

        $array = [
            $this->getResourceUrl().'/'.$committee->id => $committee->name,
            $this->getResourceUrl().'/'.$committee->id.'/'.$view_record->evaluator_id.'/evaluation-periods' => $evaluator->fullname,
            $this->getResourceUrl().'/'.$committee->id.'/'.$view_record->evaluator_id.'/'.$view_record->evaluation_id.'/evaluation-periods/evaluation-members' => $evaluation_period,
            0 => $view_record->form->shortcode,

        ];
        return $array;
    }

    public $assesment_answer,$rating_scale_values_id,$remarks,$other_comments_ans,$attendance_answer,$attendance_rating;
    public function mount(): void
    {
        $record = TrusteeHasEvaluation::find($this->record_id);

        $data = array_merge(
            ['assesment_answer' => $record->assesment_answer->mapWithKeys(function ($item) {
                    return [
                        $item->questionnaire_id => [
                            'rating_scale_values_id' => $item->rating_scale_values_id,
                            'remarks' => $item->remarks
                        ]
                    ];
                })->toArray()],
            ['attendance_answer' => $record->attendance_answer->mapWithKeys(function ($item) {
                    $item['attendance_rating'] = $item->attendance_rating_scale_values_id;
                    return [
                        $item->meeting_id => collect($item)->map(function ($value){
                            return $value ?? 0;
                        })
                    ];
                })->toArray()],
            ['other_comments_ans' => $record->other_comments->mapWithKeys(function ($item) {
                    return [
                        $item->comment_id => $item
                    ];
                })->toArray()]
        );

        $this->form->fill($data);
    }

    public function getFormSchema(): array
    {
        $view_record = TrusteeHasEvaluation::find($this->record_id);

        return [
            Grid::make(1)->schema(AssessmentEvaluationFields::run($view_record->ef_id,$this->record_id)),
            Grid::make(1)->schema(AttendanceEvaluationFields::run($view_record->ef_id,$this->record_id)),
            Grid::make(1)->schema(OtherCommentsFields::run($view_record->ef_id,$this->record_id)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Save Changes')->requiresConfirmation()
                ->action(function (){

                    $record = TrusteeHasEvaluation::find($this->record_id);

                    $data = $this->form->getState();

                    $data['trustee_evaluation_id'] = $record->evaluation_id;

                    if(isset($data['assesment_answer'])){
                        SaveAssessmentEvaluation::run($data,$record);
                    }

                    if(isset($data['attendance_answer'])){
                        SaveAttendanceEvaluation::run($data,$record);
                    }

                    if(isset($data['other_comments_ans'])){
                        SaveOtherComments::run($data,$record);
                    }

                    Notification::make()
                        ->title('Changes Saved')
                        ->success()
                        ->body('Evaluation answers have been updated successfully.')
                        ->send();
                }),
        ];
    }



    public function table(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->query(ActivityLogModel::query()->with(['causer', 'subject'])
                ->whereHasMorph('subject', [
                    QuestionaireAnswer::class,
                    AttendanceAnswer::class,
                    OtherCommentAnswer::class,
                ], function ($query) {
                    $query->where('trustee_evaluation_id', $this->record_id);
                })
                ->where(function ($query) {
                    $query->where('event', '!=', 'created')
                        ->orWhere(function ($query) {
                            $query->where('event', 'created')
                                ->where(function ($query) {
                                    $query->whereNot('subject_type', QuestionaireAnswer::class)
                                        ->orWhereHasMorph('subject', [
                                            QuestionaireAnswer::class,
                                        ], function ($q) {
                                            $q->where('trustee_evaluation_id', $this->record_id)
                                                ->where(function ($q) {
                                                    $q->whereNotNull('rating_scale_values_id')
                                                        ->orWhereNotNull('remarks');
                                                });
                                        });
                                });
                        });
                })
                ->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M j, H:i')
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('N/A'),
                TextColumn::make('subject_name')
                    ->label('Subject')
                    ->getStateUsing(function (ActivityLogModel $record): ?string {
                        if (! $record->subject_type) {
                            return null;
                        }

                        $subjectType = Str::headline(class_basename($record->subject_type));

                        if (! $record->subject) {
                            return "{$subjectType} ID {$record->subject_id}";
                        }

                        // Resolve the field name from protected/public $activitySubjectName
                        // and show its attribute
                        if (property_exists($record->subject, 'activitySubjectName')) {
                            $fieldName = (function () {
                                return $this->activitySubjectName;
                            })->call($record->subject);

                            $value = $record->subject->getAttribute($fieldName);
                            if (filled($value)) {
                                return "{$subjectType}: {$value}";
                            }
                        }

                        // Fallback to checking for 'name' property
                        if (isset($record->subject->name)) {
                            return "{$subjectType}: {$record->subject->name}";
                        }

                        // Final fallback
                        return "{$subjectType} ID {$record->subject_id}";
                    })
                    ->placeholder('N/A'),
                TextColumn::make('causer_name')
                    ->label('Causer')
                    ->getStateUsing(function (ActivityLogModel $record): ?string {
                        if (!$record->causer) {
                            return 'System';
                        }

                        // For User models, use the name accessor
                        if ($record->causer instanceof \App\Models\User) {
                            return $record->causer->name;
                        }

                        // Fallback for other models
                        return $record->causer->name ?? "#{$record->causer_id}";
                    })
                    ->placeholder('System')
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Subject Type')
                    ->options(fn (): array => ActivityLogModel::query()
                        ->whereNotNull('subject_type')
                        ->select('subject_type')
                        ->distinct()
                        ->pluck('subject_type')
                        ->filter()
                        ->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('causer_id')
                    ->label('Causer User')
                    ->options(fn (): array => \App\Models\User::query()
                        ->whereIn('id', ActivityLogModel::query()
                            ->where('causer_type', \App\Models\User::class)
                            ->whereNotNull('causer_id')
                            ->select('causer_id')
                            ->distinct()
                        )
                        ->orderBy('first_name')
                        ->get(['id', 'first_name', 'last_name'])
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->options(fn (): array => \App\Models\User::query()
                        ->whereIn('id', ActivityLogModel::query()
                            ->where('causer_type', \App\Models\User::class)
                            ->whereNotNull('causer_id')
                            ->select('causer_id')
                            ->distinct()
                        )
                        ->orderBy('first_name')
                        ->get(['id', 'first_name', 'last_name'])
                        ->mapWithKeys(fn ($user) => [
                            $user->id => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Unnamed User',
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->query(function ($query, $state): void {
                        if (filled($state['value'])) {
                            $query
                                ->where('causer_type', \App\Models\User::class)
                                ->where('causer_id', $state['value']);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('viewProperties')
                    ->label('View Properties')
                    ->modalHeading('Properties')
                    ->modalContent(function (ActivityLogModel $record): HtmlString {
                        $state = $record->properties;

                        if (empty($state)) {
                            return new HtmlString('<div class="text-gray-500">N/A</div>');
                        }

                        $properties = is_string($state) ? json_decode($state, true) : $state;
                        if ($properties === null && json_last_error() !== JSON_ERROR_NONE) {
                            $properties = $state;
                        }

                        $json = json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        return new HtmlString('<div class="text-sm whitespace-pre-wrap font-mono">'.e((string) $json).'</div>');
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label('Close')->color('primary')),
            ])
            ->paginated([10]);
    }

}
