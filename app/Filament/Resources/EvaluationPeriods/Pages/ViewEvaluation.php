<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Actions\CheckEvaluationCompleted;
use App\Actions\Form\AssessmentEvaluationFields;
use App\Actions\Form\OtherCommentsFields;
use App\Actions\SaveAssessmentEvaluation;
use App\Actions\SaveOtherComments;
use App\Filament\Resources\Committees\Pages\EvaluationMembers;
use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
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

class ViewEvaluation extends Page implements HasForms, HasTable
{

    protected static string $resource = EvaluationPeriodResource::class;

    use InteractsWithForms;
    use InteractsWithTable;

    public $evaluation_id, $record_id;

    public $assesment_answer,$rating_scale_values_id,$remarks,$other_comments_ans;

    public bool $requiresValidation = false;

    protected $queryString = ['evaluation_id', 'record_id', 'evaluator_id'];

    protected string $view = 'filament.resources.evaluation-periods.pages.view-evaluation';

    public function getTitle(): string|Htmlable
    {
        $view_record = TrusteeHasEvaluation::find($this->record_id);

        return new HtmlString($view_record->form->title."<br><br>Status: <i>".$view_record->eval_status->name."</i>");
    }

    public function getBreadcrumbs(): array
    {
        $view_record = TrusteeHasEvaluation::find($this->record_id);
        $evaluator = User::find($view_record->evaluator_id);
        $evaluation = EvaluationPeriod::find($view_record->evaluation_id);
        $evaluation_period = Carbon::parse($evaluation->date_from)->format('M d Y').' - '.Carbon::parse($evaluation->date_to)->format('M d Y');

        $array = [
            EvaluationPeriodResource::getUrl('index') => 'Evaluation Periods',
            EvaluationPeriodResource::getUrl('view', ['record' => $evaluation->id]) => $evaluation_period,
            0 => $evaluator->getFullNameAttribute() . ' - ' . $view_record->form->shortcode,
        ];
        return $array;
    }

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
            ['other_comments_ans' => $record->other_comments->mapWithKeys(function ($item) {
                    return [
                        $item->comment_id => $item
                    ];
                })->toArray()]
        );

        $this->form->fill($data);
    }

    public function updated() : void
    {

        $this->requiresValidation = false;

        $data = $this->form->getState();

        $record = TrusteeHasEvaluation::find($this->record_id);
        if($record->trustee_evaluation_statuses_id == 3){
            $record->trustee_evaluation_statuses_id = 1;
            $record->update();
        }
        $data['trustee_evaluation_id'] = $record->evaluation_id;

        if(isset($data['assesment_answer'])){
            SaveAssessmentEvaluation::run($data,$record);
        }

        if(isset($data['other_comments_ans'])){
            SaveOtherComments::run($data,$record);
        }
    }

    public function getFormSchema(): array
    {
        $view_record = TrusteeHasEvaluation::find($this->record_id);
        $eval_status = new EvaluationMembers();
        $eval_status = $eval_status->editable_field_status($view_record);
        return [
            Grid::make(1)->schema(AssessmentEvaluationFields::run($view_record->ef_id,$this->record_id,$this))->disabled(fn () => ($eval_status ) ? false : true),
            Grid::make(1)->schema(OtherCommentsFields::run($view_record->ef_id,$this->record_id))->disabled(fn () => ($eval_status ) ? false : true),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Mark as Reviewed')
                ->requiresConfirmation()
                ->visible(function (){
                    $record = TrusteeHasEvaluation::find($this->record_id);
                    if($record->trustee_evaluation_statuses_id == 4){ // 4 = For Review
                        return true;
                    }
                    return false;
                })
                ->action(function (){
                    $record = TrusteeHasEvaluation::find($this->record_id);

                    $record->update([
                        'trustee_evaluation_statuses_id' => 5 // 5 = Reviewed
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Evaluation successfully marked as reviewed')
                        ->send();
                }),
            Action::make('Mark as For Review')
                ->requiresConfirmation()
                ->visible(function (){
                    $record = TrusteeHasEvaluation::find($this->record_id);
                    if($record->trustee_evaluation_statuses_id == 2){ // 2 = Submitted
                        return true;
                    }
                    return false;
                })
                ->color('info')
                ->action(function (){
                    $record = TrusteeHasEvaluation::find($this->record_id);

                    $record->update([
                        'trustee_evaluation_statuses_id' => 4 // 4 = For Review
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Evaluation successfully marked as for review')
                        ->send();
                }),
            Action::make('Submit')
                ->requiresConfirmation()
                ->visible(function (){
                    $record = TrusteeHasEvaluation::find($this->record_id);

                    if($record->trustee_evaluation_statuses_id == 1 || $record->trustee_evaluation_statuses_id == 3){ // 1 = In Progress | 3 = Pending
                        return true;
                    }

                    return false;
                })
                ->action(function (){
                    $record = TrusteeHasEvaluation::find($this->record_id);

                    if(CheckEvaluationCompleted::run($record->ef_id,$record->id) ){

                        $record->update([
                            'trustee_evaluation_statuses_id' => 2 // 2 = Submitted
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Evaluation successfully marked as submitted')
                            ->body('The evaluation has been successfully completed and submitted.')
                            ->send();
                    }else{
                        Notification::make()
                            ->danger()
                            ->title('Evaluation Incomplete')
                            ->body('Please complete all evaluation fields before submitting this record.')
                            ->send();

                        $this->requiresValidation = true;

                        $this->unmountAction();

                        $this->form->validate();
                    }
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Activity Log')
            ->poll('30s')
            ->query(ActivityLogModel::query()->with(['causer', 'subject'])
                ->whereHasMorph('subject', [
                    QuestionaireAnswer::class,
                    OtherCommentAnswer::class,
                ], function ($query) {
                    $query->where('trustee_evaluation_id', $this->record_id);
                })
                ->orWhereHasMorph('subject', [
                    TrusteeHasEvaluation::class,
                ], function ($query) {
                    $query->where('id', $this->record_id);
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

                        $labelMap = [
                            'TrusteeHasEvaluation' => 'Evaluation Status',
                            'OtherCommentAnswer' => 'Other Comments',
                            'QuestionaireAnswer' => 'Assessment Evaluation',
                            'AttendanceAnswer' => 'Attendance Evaluation',
                        ];
                        $subjectType = Str::headline($labelMap[class_basename($record->subject_type)]);

                        // Final fallback
                        return "{$subjectType}";
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
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Subject Type')
                    ->options([
                        QuestionaireAnswer::class => Str::headline(class_basename(QuestionaireAnswer::class)),
                        OtherCommentAnswer::class => Str::headline(class_basename(OtherCommentAnswer::class)),
                    ])
                    ->searchable()
                    ->preload(),
                SelectFilter::make('causer_id')
                    ->label('Causer User')
                    ->options(fn (): array => \App\Models\User::query()
                        ->whereIn('id', ActivityLogModel::query()
                            ->where('causer_type', \App\Models\User::class)
                            ->whereNotNull('causer_id')
                            ->whereHasMorph('subject', [
                                QuestionaireAnswer::class,
                                OtherCommentAnswer::class,
                            ], function ($query) {
                                $query->where('trustee_evaluation_id', $this->record_id);
                            })
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
                            return new HtmlString('<div class="text-gray-500 p-4">No changes recorded.</div>');
                        }

                        // Handle Collection, array, or string
                        if ($state instanceof \Illuminate\Support\Collection) {
                            $properties = $state->toArray();
                        } elseif (is_string($state)) {
                            $properties = json_decode($state, true);
                        } else {
                            $properties = (array) $state;
                        }

                        if (!is_array($properties)) {
                            return new HtmlString('<div class="text-gray-500 p-4">Unable to display changes.</div>');
                        }

                        $labelMap = [
                            'eval_status.name'                => 'Evaluation Status',
                            'questionnaire.name'                => 'Questionnaire',
                            'ratingScaleValue.value'            => 'Rating Value',
                            'ratingScaleValue.qualitative'      => 'Qualitative Rating',
                            'ratingScaleValue.name'             => 'Rating Name',
                            'remarks'                           => 'Remarks',
                            'comment'                           => 'Comment',
                        ];

                        $formatValue = fn($value) => is_null($value)
                            ? '<span class="text-gray-400 italic">Empty</span>'
                            : e($value);

                        $html = '<div class="p-4 space-y-4 text-sm">';

                        // Show updated attributes (old vs new)
                        if (!empty($properties['old']) && !empty($properties['attributes'])) {
                            $html .= '<div>';
                            $html .= '<p class="font-semibold text-gray-700 mb-2">Changes Made:</p>';
                            $html .= '<table class="w-full border-collapse">';
                            $html .= '<thead><tr>
                    <td class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/3">Field</td>
                    <td class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/3">Old Value</td>
                    <td class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/3">New Value</td>
                  </tr></thead>';
                            $html .= '<tbody>';

                            foreach ($properties['attributes'] as $key => $newValue) {
                                $oldValue = $properties['old'][$key] ?? null;

                                // Skip if both old and new values are the same
                                if ($oldValue === $newValue) {
                                    continue;
                                }

                                $label = $labelMap[$key] ?? str($key)->replace('_', ' ')->replace('.', ' ')->title();
                                $html .= '<tr class="border-t border-gray-100">';
                                $html .= '<td class="py-1 px-2 font-medium text-gray-700">' . e($label) . '</td>';
                                $html .= '<td class="py-1 px-2 text-red-500">' . $formatValue($oldValue) . '</td>';
                                $html .= '<td class="py-1 px-2 text-green-600">' . $formatValue($newValue) . '</td>';
                                $html .= '</tr>';
                            }

                            $html .= '</tbody></table>';
                            $html .= '</div>';
                        }

                        // Show created attributes
                        elseif (!empty($properties['attributes'])) {
                            $html .= '<div>';
                            $html .= '<p class="font-semibold text-gray-700 mb-2">Created With:</p>';
                            $html .= '<table class="w-full border-collapse">';
                            $html .= '<thead><tr>
                    <th class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/2">Field</th>
                    <th class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/2">Value</th>
                  </tr></thead>';
                            $html .= '<tbody>';

                            foreach ($properties['attributes'] as $key => $value) {
                                $label = $labelMap[$key] ?? str($key)->replace('_', ' ')->replace('.', ' ')->title();
                                $html .= '<tr class="border-t border-gray-100">';
                                $html .= '<td class="py-1 px-2 font-medium text-gray-700">' . e($label) . '</td>';
                                $html .= '<td class="py-1 px-2 text-gray-800">' . $formatValue($value) . '</td>';
                                $html .= '</tr>';
                            }

                            $html .= '</tbody></table>';
                            $html .= '</div>';
                        }

                        else {
                            $html .= '<div class="text-gray-500">No changes recorded.</div>';
                        }

                        $html .= '</div>';

                        return new HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label('Close')->color('primary')),
            ])
            ->paginated([10]);
    }
}
