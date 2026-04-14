<?php

namespace App\Actions\Form;

use App\Models\CommitteeHasTrustee;
use App\Models\EvaluationPeriod;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;
use Lorisleiva\Actions\Concerns\AsAction;

class AttendanceForm
{
    use AsAction;

    public function handle($evaluation_period = null , $trustees = [])
    {
        // $evaluation_period = EvaluationPeriod::active()->first();
        // $trustees = $evaluation_period->assignments()->get();
        // dd($trustees);

        // $trustee_attendance = [];
        // foreach($trustees as $trustee){
        // $commiteeIDs = $evaluation_period->assignments->groupBy('committee_id');


        $participating_committees = $evaluation_period->assignments
            ->groupBy('committee_id');

        $committees = [];
        foreach($participating_committees as $commitee){
            $com_details = $commitee->first()->committee;
            $members = [];
            $commitee = $commitee->groupBy('evaluator_id');
            foreach($commitee as $member){
                $member = $member->first();
                $members[] = [
                    'id' => $member->evaluator_id,
                    'name' => $member->evaluator->full_name,
                    'commitee_id' => $com_details->id,
                    'total_meetings' => 0,
                    'physically_present' => 0,
                    'considered_present' => 0,
                    'total_present' => 0,
                    'attendance_rating_scale_values_id' => 0,
                ];
            }
            $committees[] = [
                'id'  => $com_details->id,
                'name' => $com_details->name,
                'members' => $members
            ];

        }
        // dd($commitees);
        // dd($evaluation_period->assignments->groupBy('committee_id'));
        // if(!$evaluation_period->attendance){

        // }


        // $committees =  [
        //     [
        //         'id' => 1,
        //         'name' =>  'Board of Trustees',
        //         'members' => [
        //             [
        //                 'id' => 1,
        //                 'name' => 'Dennis',
        //                 'commitee_id' => 1,
        //                 'total_meetings' => 0,
        //                 'physically_present' => 0,
        //                 'considered_present' => 0,
        //                 'total_present' => 0,
        //                 'attendance_rating_scale_values_id' => 1,
        //             ],

        //             [
        //                 'id' => 2,
        //                 'name' => 'Demarcus Cousins',
        //                 'total_meetings' => 0,
        //                 'commitee_id' => 1,
        //                 'physically_present' => 0,
        //                 'considered_present' => 0,
        //                 'total_present' => 0,
        //                 'attendance_rating_scale_values_id' => 1,
        //             ],

        //             [
        //                 'id' => 3,
        //                 'name' => 'Zaza Pachulia',
        //                 'total_meetings' => 0,
        //                 'commitee_id' => 1,
        //                 'physically_present' => 2,
        //                 'considered_present' => 3,
        //                 'total_present' => 0,
        //                 'attendance_rating_scale_values_id' => 1,
        //             ],

        //         ],
        //     ],
        //     [
        //         'id' => 2,
        //         'name' =>  'Governance',
        //         'members' => [
        //             [
        //                 'id' => 1,
        //                 'name' => 'Dennis',
        //                 'commitee_id' => 2,
        //                 'total_meetings' => 0,
        //                 'physically_present' => 0,
        //                 'considered_present' => 0,
        //                 'total_present' => 0,
        //                 'attendance_rating_scale_values_id' => 1,
        //             ],

        //             [
        //                 'id' => 2,
        //                 'name' => 'Jane Doe',
        //                 'total_meetings' => 0,
        //                 'commitee_id' => 2,
        //                 'physically_present' => 0,
        //                 'considered_present' => 0,
        //                 'total_present' => 0,
        //                 'attendance_rating_scale_values_id' => 1,
        //             ],

        //         ],
        //     ]
        // ];

        // // $committees =  collect($committees)->map(function ($item) {
        // //     return is_array($item) ? collect($item) : $item;
        // // });
        $tabs = [];
        foreach($committees as $commitee){
            $sections = [];

            foreach ($commitee['members'] as $member) {
                $fieldsets = [

                    TextInput::make('commitee.'.$commitee['id'].'.members.'.$member['id'].'.total_meetings.value')
                        ->minValue(0)
                        ->required()
                        ->default(0)
                        ->label('Total Meetings')
                        ->numeric(),

                    TextInput::make('commitee.'.$commitee['id'].'.members.'.$member['id'].'.physically_present.value')
                        ->numeric()
                        ->minValue(0)
                        ->label('Physically Present')
                        ->reactive()
                        ->mask(RawJs::make('$input.replace(/[^0-9]/g, "")'))
                        ->afterStateUpdated(function ($state, callable $get, callable $set) use ($commitee, $member) {
                            $base = 'commitee.'.$commitee['id'].'.members.'.$member['id'];
                            $physical = (int) $get($base.'.physically_present.value');
                            $considered = (int) $get($base.'.considered_present.value');
                            $set($base.'.total_present.value', $physical + $considered);
                        }),

                    TextInput::make('commitee.'.$commitee['id'].'.members.'.$member['id'].'.considered_present.value')
                        ->numeric()
                        ->label('Considered Present')
                        ->minValue(0)
                        ->mask(RawJs::make('$input.replace(/[^0-9]/g, "")'))
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) use ($commitee, $member) {
                            $base = 'commitee.'.$commitee['id'].'.members.'.$member['id'];
                            $physical = (int) $get($base.'.physically_present.value');
                            $considered = (int) $get($base.'.considered_present.value');
                            $set($base.'.total_present.value', $physical + $considered);
                        }),

                    TextInput::make('commitee.'.$commitee['id'].'.members.'.$member['id'].'.total_present.value')
                        ->numeric()
                        ->label('Total Number of Attendance')
                        ->readOnly()
                        ->default(0)
                        ->rules([
                            function (callable $get) use ($commitee, $member) {
                                return function (string $attribute, $value, \Closure $fail) use ($get, $commitee, $member) {

                                    $base = 'commitee.'.$commitee['id'].'.members.'.$member['id'];

                                    $totalMeetings = (int) $get($base.'.total_meetings.value');
                                    $physical = (int) $get($base.'.physically_present.value');
                                    $considered = (int) $get($base.'.considered_present.value');

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
                ];
                // dd($fieldsets);

                $sections[] = Section::make($member['name'])
                        ->columns(4)
                        ->extraAttributes(['class' => 'p-0 m-0 testing'], true)
                        ->schema($fieldsets);

            }

            $tabs[] = Tab::make($commitee['name'])
                ->schema(
                    $sections
                );
        }

        $test = Tabs::make('Tabs')
            ->tabs($tabs);


        return [$test];
        // ...
    }
}
