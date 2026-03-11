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
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class EvaluationMembers extends ListRecords
{
    protected static string $resource = CommitteeResource::class;

    public $record,$evaluator_id,$evaluation_id;
    protected $queryString = ['record','evaluator_id','evaluation_id'];
    protected static ?string $title = 'Evaluation Members';

    public function getBreadcrumbs(): array
    {
        $evaluator = User::find($this->evaluator_id);
        $evaluation = EvaluationPeriod::find($this->evaluation_id);
        $evaluation_period = Carbon::parse($evaluation->date_from)->format('M d Y').' - '.Carbon::parse($evaluation->date_to)->format('M d Y');
        $committee = Committee::find($this->record);
        $array = [
            $this->getResourceUrl().'/'.$this->record => $committee->name,
            $this->getResourceUrl().'/'.$this->record.'/'.$this->evaluator_id.'/evaluation-periods' => $evaluator->fullname,
            '0' => $evaluation_period
        ];
        return $array;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected function getTableQuery(): Builder|Relation|null
    {
        return TrusteeHasEvaluation::query()->where('committee_id', $this->record)->where('evaluator_id', $this->evaluator_id)->where('evaluation_id', $this->evaluation_id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.shortcode'),
                TextColumn::make('evaluator.name'),
                TextColumn::make('member.name'),
                TextColumn::make('eval_status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'primary',
                        'Pending' => 'warning',
                        'Locked' => 'success',
                        'For Review' => 'info',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                ViewAction::make()->authorize(check_committee_permission($this->record,'View:Committee'))
                    ->url(fn(Model $record): string => CommitteeResource::getUrl('view-evaluation', ['record' => $this->record,'record_id' => $record->id])),
                DeleteAction::make()->authorize(check_committee_permission($this->record,'Delete:Committee')),
                ActionGroup::make([
                        Action::make('Evaluate Assessment')
                            ->visible(fn(Model $record) => check_eval_form_sections($record->ef_id,1)) // 1 = Section Type: Assessment
                            ->closeModalByClickingAway(false)
                            ->modalheading(fn(Model $record): string => $record->member ? 'Person being evaluated: '.strtoupper($record->member->fullname) : 'Evaluate Assessment')
                            ->schema(fn(Model $record) =>  AssessmentEvaluationFields::run($record->ef_id, $record->id))
                            ->fillForm(fn (Model $record) =>
                                ['assesment_answer' => $record->assesment_answer->mapWithKeys(function ($item) {
                                    return [
                                        $item->questionnaire_id => [
                                            'rating_scale_values_id' => $item->rating_scale_values_id,
                                            'remarks' => $item->remarks
                                        ]
                                    ];
                                })->toArray()]
                            )
                            ->action(function (array $data, Model $record){

                                SaveAssessmentEvaluation::run($data,$record);

                                Notification::make()
                                    ->title('Attendance Evaluation Submitted')
                                    ->success()
                                    ->body('Your attendance evaluation has been successfully submitted.')
                                    ->send();
                            })
                            ->authorize(check_committee_permission($this->record,'AssessmentEvaluation:Committee'))
                            ->icon(Heroicon::OutlinedClipboardDocumentCheck),


                        Action::make('Evaluate Attendance')
                            ->modalWidth(Width::SixExtraLarge)
                            ->closeModalByClickingAway(false)
                            ->modalheading(fn(Model $record): string => $record->member ? 'Person being evaluated: '.strtoupper($record->member->fullname) : 'Evaluate Assessment')
                            ->schema(fn(Model $record) => AttendanceEvaluationFields::run($record->ef_id,$record->id))
                            ->fillForm(fn (Model $record) =>
                                ['attendance_answer' => $record->attendance_answer->mapWithKeys(function ($item) {
                                    $item['attendance_rating'] = $item->attendance_rating_scale_values_id;
                                    return [
                                        $item->meeting_id => collect($item)->map(function ($value){
                                            return $value ?? 0;
                                        })
                                    ];
                                })->toArray()]
                            )
                            ->action(function (array $data, Model $record){

                                SaveAttendanceEvaluation::run($data,$record);

                                Notification::make()
                                    ->title('Attendance Evaluation Submitted')
                                    ->success()
                                    ->body('Your attendance evaluation has been successfully submitted.')
                                    ->send();
                            })
                            ->visible(fn(Model $record) => check_eval_form_sections($record->ef_id,2)) // 2 = Section Type: Attendance
                            ->authorize(check_committee_permission($this->record,'AttendanceEvaluation:Committee'))
                            ->icon(Heroicon::OutlinedClipboardDocumentList),

                        Action::make('Other Comments')
                            ->closeModalByClickingAway(false)
                            ->visible(fn(Model $record) => check_eval_form_sections($record->ef_id,3)) // 3 = Section Type: Other Comments
                            ->schema(fn(Model $record) => OtherCommentsFields::run($record->ef_id, $record->id))
                            ->fillForm(fn (Model $record) =>
                                ['other_comments_ans' => $record->other_comments->mapWithKeys(function ($item) {
                                    return [
                                        $item->comment_id => $item
                                    ];
                                })->toArray()]
                            )
                            ->action(function (array $data, Model $record){

                                SaveOtherComments::run($data,$record);
                                foreach($data['other_comments_ans'] as $index => $answer){
                                    OtherCommentAnswer::updateOrCreate(
                                        [
                                            'trustee_evaluation_id' => $record->id,
                                            'comment_id' => $index,
                                        ],
                                        [
                                            'comment' => $answer['comment'],
                                        ]
                                    );
                                }

                                Notification::make()
                                    ->title('Evaluation Submitted')
                                    ->success()
                                    ->body('Your other comments recorded.')
                                    ->send();
                            })
                            ->authorize(check_committee_permission($this->record,'OtherComments:Committee'))
                            ->icon(Heroicon::OutlinedChatBubbleLeft),

                        Action::make('Print')
                            ->authorize(check_committee_permission($this->record,'PrintEvaluation:Committee'))
                            ->openUrlInNewTab()
                            ->url(fn(Model $record) => route('queues-call-next', ['trustee_evaluation_id' => $record->id]))
                            ->icon(Heroicon::OutlinedPrinter),
                    ])
                    ->label('Evaluation actions')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button()
            ])
            ->toolbarActions([
//                BulkActionGroup::make([
//                    DetachBulkAction::make(),
//                    DeleteBulkAction::make(),
//                ]),
            ]);
    }
}
