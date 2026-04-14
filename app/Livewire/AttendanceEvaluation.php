<?php

namespace App\Livewire;

use App\Models\AttendanceAnswer;
use App\Models\EvaluationPeriod;
use App\Models\Committee;
use App\Models\RatingScale;
use App\Models\RatingScaleValue;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use App\Actions\AssesmentComputation;
use App\Actions\Form\AttendanceForm;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Filters\SelectFilter;

use function PHPUnit\Framework\isEmpty;

class AttendanceEvaluation extends TableWidget
{
    public $evaluation_period_id;
    public $evaluation_period;

    protected int | string | array $columnSpan = 'full';

    public function mount() : void {
        $this->evaluation_period = EvaluationPeriod::find($this->evaluation_period_id);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return 'Attendance Evaluation';
    }

    public function table(Table $table): Table
    {
        $query = AttendanceAnswer::where('evaluation_period_id',$this->evaluation_period_id);


        return $table
            ->query(fn (): Builder => $query)
            ->columns([

                TextColumn::make('trustee.full_name')
                    ->label('Member')
                    // ->searchable()
                    ->sortable(),

                TextColumn::make('ratingScaleValue.name')
                    ->label('Attendance Rating')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),


                TextColumn::make('commitee.name')
                    ->label('Commitee')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),


                TextColumn::make('total_meetings')
                    ->label('Total Meetings')
                    ->sortable(),
                TextColumn::make('physically_present')
                    ->label('Physically Present')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('considered_present')
                    ->label('Considered Present')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('total_present')
                    ->label('Total Present')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('committee_id')
                    ->relationship('commitee', 'name'),
                SelectFilter::make('trustee_id')
                    ->searchable()
                    ->preload()
                    ->relationship('trustee', 'name'),
            ])
            ->headerActions([
                Action::make('evaluate_attendance')
                        ->modalWidth(Width::SixExtraLarge)
                        ->closeModalByClickingAway(false)
                        ->color('primary')
                        ->modalheading(function() : string {

                            $test = EvaluationPeriod::find($this->evaluation_period_id);

                            $string  =  $test ?  $test->formatted_coverage : ' ';
                            $string = 'Attendance Evaluation for Period: '. $string;
                            return $string;
                        })
                        ->schema(function (){

                            return [
                                Grid::make(1)->schema(AttendanceForm::run($this->evaluation_period))
                            ];
                        })
                        ->modalSubmitAction(function () {
                            //EvaluationPeriod $evaluation_period_id
                            // if($record->trustee_evaluation_statuses_id == 2 || $record->trustee_evaluation_statuses_id == 4 || $record->trustee_evaluation_statuses_id == 5){
                            //     // 2 = Lock | 4 = For Review | 5 = Review
                            //     return false;
                            // }

                        })
                        ->fillForm(function () {
                            $test = EvaluationPeriod::find($this->evaluation_period_id);
                            $fillForm = [];
                            foreach($test->attendance as $attendance){
                                $fillForm['commitee'][$attendance->committee_id]['members'][$attendance->trustee_id]['total_meetings']['value'] =  $attendance->total_meetings;
                                $fillForm['commitee'][$attendance->committee_id]['members'][$attendance->trustee_id]['physically_present']['value'] =  $attendance->physically_present;
                                $fillForm['commitee'][$attendance->committee_id]['members'][$attendance->trustee_id]['considered_present']['value'] =  $attendance->considered_present;
                                $fillForm['commitee'][$attendance->committee_id]['members'][$attendance->trustee_id]['total_present']['value'] =  $attendance->total_present;
                            }
                            return $fillForm;
                        })
                        ->action(function (array $data){
                            $evaluationPeriod = EvaluationPeriod::find($this->evaluation_period_id);

                            foreach($data['commitee'] as $commitee_index=> $committee){
                                foreach($committee['members'] as $member_index => $members ){
                                    $percentage = AssesmentComputation::get_attendance_percentage($members['total_meetings']['value'], $members['total_present']['value']);
                                    $rating = AssesmentComputation::get_attendance_rating($percentage);

                                    if(!$rating){
                                        Notification::make()
                                            ->title('Error')
                                            ->danger()
                                            ->body('Saving Error!. Please check encoded attendance')
                                            ->send();
                                        return;
                                    }


                                    $answer = [
                                        'total_meetings' => $members['total_meetings']['value'],
                                        'physically_present' => $members['physically_present']['value'],
                                        'considered_present' => $members['considered_present']['value'],
                                        'total_present' => $members['total_present']['value'],
                                        'attendance_rating_scale_values_id' => $rating?->id ?? null,
                                        'committee_id' => $commitee_index,
                                        'trustee_id' => $member_index,
                                        'evaluation_period_id' => $evaluationPeriod->id
                                    ];

                                    AttendanceAnswer::updateOrCreate(
                                        [
                                            'committee_id' => $commitee_index,
                                            'trustee_id' => $member_index,
                                            'evaluation_period_id' => $answer['evaluation_period_id'],
                                        ],
                                        $answer,
                                    );

                                }
                            }

                            Notification::make()
                                ->title('Attendance Evaluation Submitted')
                                ->success()
                                ->body('Your attendance evaluation has been successfully submitted.')
                                ->send();
                        })
                        ->icon(Heroicon::OutlinedClipboardDocumentList),
                //
            ])
            ->recordActions([
                EditAction::make()
                ->mutateDataUsing(function (array $data): array {

                    $percentage = AssesmentComputation::get_attendance_percentage($data['total_meetings'], $data['total_present']);
                    $rating = AssesmentComputation::get_attendance_rating($percentage);
                    $data['attendance_rating_scale_values_id'] = $rating->id;

                    return $data;
                })
                ->schema([
                    Placeholder::make('trustee.full_name')->weight(FontWeight::Bold),
                    Placeholder::make('commitee.name')->label('Committee')->weight(FontWeight::Bold),
                    TextInput::make('total_meetings')
                        ->minValue(0)
                        ->required()
                        ->default(0)
                        ->label('Total Meetings')
                        ->numeric(),
                    TextInput::make('physically_present')
                        ->numeric()
                        ->minValue(0)
                        ->label('Physically Present')
                        ->reactive()
                        ->mask(RawJs::make('$input.replace(/[^0-9]/g, "")'))
                        ->afterStateUpdated(function ($state, callable $get, callable $set){
                            $physical = (int) $get('physically_present');
                            $considered = (int) $get('considered_present');
                            $set('total_present', $physical + $considered);
                        }),
                    TextInput::make('considered_present')
                        ->numeric()
                        ->minValue(0)
                        ->label('Considered Present')
                        ->reactive()
                        ->mask(RawJs::make('$input.replace(/[^0-9]/g, "")'))
                        ->afterStateUpdated(function ($state, callable $get, callable $set){
                            $physical = (int) $get('physically_present');
                            $considered = (int) $get('considered_present');
                            $set('total_present', $physical + $considered);
                        }),
                    TextInput::make('total_present')
                        ->numeric()
                        ->label('Total Number of Attendance')
                        ->readOnly()
                        ->default(0)
                        ->rules([
                            function (callable $get){
                                return function (string $attribute, $value, \Closure $fail) use ($get) {

                                    $physical = (int) $get('physically_present');
                                    $considered = (int) $get('considered_present');
                                    $totalMeetings = (int) $get('total_meetings');

                                    $totalPresent = $physical + $considered;

                                    if ($totalPresent > $totalMeetings) {
                                        $fail('Total present cannot exceed total meetings.');
                                    }

                                    if ($physical > $totalMeetings) {
                                        $fail('Physically present cannot exceed total meetings.');
                                    }

                                    if ($considered > $totalMeetings) {
                                        $fail('Considered present cannot exceed total meetings.');
                                    }
                                };
                            }
                        ])
                        ->dehydrated(true)
                ]),

                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
