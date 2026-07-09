<?php

namespace App\Livewire;

use App\Models\AttendanceAnswer;
use App\Models\EvaluationPeriod;
use App\Models\Committee;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use App\Actions\AssesmentComputation;
use App\Actions\Form\AttendanceForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
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

    public static function canView(): bool
    {
        return auth()->user()->can("Attendance:EvaluationPeriod");
    }

    public function table(Table $table): Table
    {
        $query = AttendanceAnswer::where('evaluation_period_id',$this->evaluation_period_id)->orderByRaw('CASE WHEN committee_id IS NULL THEN 0 ELSE 1 END')->orderBy('id');


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
                    ->label('Category')
                    ->badge()
                    ->color('warning')
                    ->default('BOT Meetings')
                    ->formatStateUsing(fn($state) => ucfirst($state ?? 'BOT Meetings'))
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
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->modalHeading(function (): string {
                        $test = EvaluationPeriod::find($this->evaluation_period_id);
                        $string = $test ? $test->formatted_coverage : ' ';
                        return 'Attendance Evaluation for Period: ' . $string;
                    })
                    ->fillForm(function (): array {
                        $evaluation_period = EvaluationPeriod::find($this->evaluation_period_id);
                        $fillForm = [];

                        foreach ($evaluation_period->attendance as $attendance) {
                            $committee_key = $attendance->committee_id ?? 'bot';

                            $fillForm['commitee'][$committee_key]['members'][$attendance->trustee_id]['total_meetings']['value']     = $attendance->total_meetings;
                            $fillForm['commitee'][$committee_key]['members'][$attendance->trustee_id]['physically_present']['value'] = $attendance->physically_present;
                            $fillForm['commitee'][$committee_key]['members'][$attendance->trustee_id]['considered_present']['value'] = $attendance->considered_present;
                            $fillForm['commitee'][$committee_key]['members'][$attendance->trustee_id]['total_present']['value']      = $attendance->total_present;
                        }

                        return $fillForm;
                    })
                    ->schema(function (): array {
                        return [
                            Grid::make(1)->schema(AttendanceForm::run($this->evaluation_period))
                        ];
                    })
                    ->modalSubmitAction(function () {
                        // return false here if you want to hide the submit button under certain conditions
                        // e.g. if record is locked:
                        // if ($this->record->trustee_evaluation_statuses_id == 2) return false;
                    })
                    ->action(function (array $data): void {
                        $evaluationPeriod = EvaluationPeriod::find($this->evaluation_period_id);

                        foreach ($data['commitee'] as $committee_key => $committee_data) {
                            $committee_id = $committee_key === 'bot' ? null : (int) $committee_key;

                            foreach ($committee_data['members'] as $member_id => $member_data) {
                                $percentage = AssesmentComputation::get_attendance_percentage(
                                    $member_data['total_meetings']['value'],
                                    $member_data['total_present']['value']
                                );

                                $rating = AssesmentComputation::get_attendance_rating($percentage);

                                if (!$rating) {
                                    Notification::make()
                                        ->title('Error')
                                        ->danger()
                                        ->body('Saving Error! Please check encoded attendance.')
                                        ->send();
                                    return;
                                }

                                AttendanceAnswer::updateOrCreate(
                                    [
                                        'trustee_id'          => $member_id,
                                        'evaluation_period_id' => $evaluationPeriod->id,
                                        'committee_id'         => $committee_id,
                                    ],
                                    [
                                        'total_meetings'                    => $member_data['total_meetings']['value'],
                                        'physically_present'                => $member_data['physically_present']['value'],
                                        'considered_present'                => $member_data['considered_present']['value'],
                                        'total_present'                     => $member_data['total_present']['value'],
                                        'attendance_rating_scale_values_id' => $rating?->id ?? null,
                                        'committee_id'                      => $committee_id,
                                        'trustee_id'                        => $member_id,
                                        'evaluation_period_id'              => $evaluationPeriod->id,
                                    ]
                                );
                            }
                        }

                        Notification::make()
                            ->title('Attendance Evaluation Submitted')
                            ->success()
                            ->body('Your attendance evaluation has been successfully submitted.')
                            ->send();
                    }),

                Action::make('add_attendee')
                    ->label('Add Attendee')
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('success')
                    ->schema([
                        Select::make('committee_id')
                            ->label('Meeting Category')
                            ->options(function () {
                                $options = ['bot' => 'BOT Meetings'];
                                Committee::all()->each(function ($committee) use (&$options) {
                                    $options[$committee->id] = $committee->name . ' Meetings';
                                });
                                return $options;
                            })
                            ->required(),

                        Select::make('trustee_id')
                            ->label('Board Member')
                            ->searchable()
                            ->preload()
                            ->options(function (Get $get) {
                                $committee_id = $get('committee_id');
                                $executive_roles = ['super admin', 'secretariat'];

                                if ($committee_id === 'bot') {
                                    // For BOT: Show all users except LRPs and executives
                                    return User::whereHas('roles', function ($query) use ($executive_roles) {
                                        $query->whereNotIn('name', array_merge($executive_roles, ['lead resource person']));
                                    })
                                    ->orWhereDoesntHave('roles')
                                    ->get()
                                    ->mapWithKeys(fn($user) => [$user->id => $user->full_name])
                                    ->toArray();
                                } elseif ($committee_id) {
                                    // For specific committee: Show only members of that committee
                                    $committee = Committee::find($committee_id);
                                    if (!$committee) {
                                        return [];
                                    }

                                    return $committee->committee_has_trustees()
                                        ->with('user')
                                        ->get()
                                        ->mapWithKeys(fn($trustee) => [$trustee->user_id => $trustee->user->full_name])
                                        ->toArray();
                                }

                                return [];
                            })
                            ->searchable([
                                'first_name',
                                'last_name',
                                'middle_name'
                            ])
                            ->required()
                            ->reactive(),
                    ])
                    ->action(function (array $data): void {
                        $evaluationPeriod = EvaluationPeriod::find($this->evaluation_period_id);
                        $committee_id = $data['committee_id'] === 'bot' ? null : (int) $data['committee_id'];
                        $trustee_id = (int) $data['trustee_id'];

                        // Validate: Check if this trustee already has attendance for this committee
                        $existingAttendance = AttendanceAnswer::where('trustee_id', $trustee_id)
                            ->where('evaluation_period_id', $evaluationPeriod->id)
                            ->where('committee_id', $committee_id)
                            ->exists();

                        if ($existingAttendance) {
                            Notification::make()
                                ->title('Duplicate Entry')
                                ->danger()
                                ->body('This attendee already has an attendance record for this meeting type.')
                                ->send();
                            return;
                        }

                        // Create new attendance record
                        AttendanceAnswer::create([
                            'trustee_id' => $trustee_id,
                            'evaluation_period_id' => $evaluationPeriod->id,
                            'committee_id' => $committee_id,
                            'total_meetings' => 0,
                            'physically_present' => 0,
                            'considered_present' => 0,
                            'total_present' => 0,
                        ]);

                        Notification::make()
                            ->title('Attendee Added')
                            ->success()
                            ->body('Attendee has been successfully added.')
                            ->send();


                    }),

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
                            ->dehydrated(true),

                        ]),
                    DeleteAction::make(),


                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function autoSave(
        string|int $committee_key,
        int $member_id,
        int $total_meetings,
        int $physically_present,
        int $considered_present,
        int $total_present
    ): void {
        try {
            $evaluationPeriod = EvaluationPeriod::find($this->evaluation_period_id);
            $committee_id = $committee_key === 'bot' ? null : (int) $committee_key;

            $percentage = AssesmentComputation::get_attendance_percentage($total_meetings, $total_present);
            $rating = AssesmentComputation::get_attendance_rating($percentage);

            if (!$rating) return;

            AttendanceAnswer::updateOrCreate(
                [
                    'trustee_id'           => $member_id,
                    'evaluation_period_id' => $evaluationPeriod->id,
                    'committee_id'         => $committee_id,
                ],
                [
                    'total_meetings'                    => $total_meetings,
                    'physically_present'                => $physically_present,
                    'considered_present'                => $considered_present,
                    'total_present'                     => $total_present,
                    'attendance_rating_scale_values_id' => $rating?->id ?? null,
                    'committee_id'                      => $committee_id,
                    'trustee_id'                        => $member_id,
                    'evaluation_period_id'              => $evaluationPeriod->id,
                ]
            );

        } catch (\InvalidArgumentException $e) {
            return;
        }
    }
}
