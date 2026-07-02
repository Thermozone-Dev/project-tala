<?php

namespace App\Actions\Form;

use App\Models\Committee;
use App\Models\TrusteeHasEvaluation;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Lorisleiva\Actions\Concerns\AsAction;

class AttendanceForm
{
    use AsAction;

    public function handle($evaluation_period = null , $trustees = [])
    {
        $committees = [];

        // 1. Create BOT Meetings tab - Get unique BOT members (where committee_id is null)
        $bot_evaluations = TrusteeHasEvaluation::where('evaluation_id', $evaluation_period->id)
            ->whereNull('committee_id')
            ->with('member.roles')
            ->get()
            ->unique('member_id');

        $bot_members = [];
        foreach($bot_evaluations as $evaluation) {
            $member = $evaluation->member;
            if(!$member){
                continue;
            }
            $role = $member?->roles?->first()?->name ?? 'Member';

            // Exclude Lead Resource Persons from BOT meetings
            if (strtolower($role) === 'lead resource person') {
                continue;
            }

            $bot_members[] = [
                'id' => $member->id,
                'name' => $member->full_name,
                'role' => $role,
                'committee_id' => null,
                'total_meetings' => 0,
                'physically_present' => 0,
                'considered_present' => 0,
                'total_present' => 0,
                'attendance_rating_scale_values_id' => 0,
            ];
        }

        if (!empty($bot_members)) {
            $committees[] = [
                'id' => 'bot',
                'name' => 'BOT Meetings',
                'members' => $bot_members
            ];
        }

        // 2. Create tabs for each committee - Get unique committee-specific members from evaluations
        $all_committees = Committee::get();

        foreach($all_committees as $committee) {
            $committee_evaluations = TrusteeHasEvaluation::where('evaluation_id', $evaluation_period->id)
                ->where('committee_id', $committee->id)
                ->with('member.roles')
                ->get()
                ->unique('member_id');

            $committee_members = [];
            foreach($committee_evaluations as $evaluation) {
                $member = $evaluation->member;
                if(!$member){
                    continue;
                }
                $committee_members[] = [
                    'id' => $member->id,
                    'name' => $member->full_name,
                    'role' => $member->roles->first()?->name ?? 'Member',
                    'committee_id' => $committee->id,
                    'total_meetings' => 0,
                    'physically_present' => 0,
                    'considered_present' => 0,
                    'total_present' => 0,
                    'attendance_rating_scale_values_id' => 0,
                ];
            }

            if (!empty($committee_members)) {
                $committees[] = [
                    'id' => $committee->id,
                    'name' => $committee->name . ' Meetings',
                    'members' => $committee_members
                ];
            }
        }

        $tabs = [];
        foreach($committees as $commitee){
            $sections = [];

            foreach ($commitee['members'] as $member) {
                $base = 'commitee.' . $commitee['id'] . '.members.' . $member['id'];

                $sections[] = Section::make($member['name'])
                    ->columns(4)
                    ->schema([

                        TextInput::make($base . '.total_meetings.value')
                            ->label('Total Meetings')
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->extraInputAttributes([
                                'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1').replace('.','')"
                            ])
                            ->afterStateUpdated(function (Get $get,Set $set, $livewire) use ($base, $commitee, $member) {
                                $physical   = (int) ($get($base . '.physically_present.value') ?? 0);
                                $considered = (int) ($get($base . '.considered_present.value') ?? 0);
                                $total      = $physical + $considered;

                                $livewire->validateOnly('mountedActions.0.data.' . $base . '.total_present.value');

                                $livewire->autoSave(
                                    $commitee['id'],
                                    $member['id'],
                                    (int) ($get($base . '.total_meetings.value') ?? 0),
                                    $physical,
                                    $considered,
                                    $total,
                                );
                            }),

                        TextInput::make($base . '.physically_present.value')
                            ->label('Physically Present')
                            ->minValue(0)
                            ->default(0)
                            ->live(onBlur: true)
                            ->extraInputAttributes([
                                'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1').replace('.','')"
                            ])
                            ->afterStateUpdated(function (Get $get, Set $set, $livewire) use ($base, $commitee, $member) {
                                $physical   = (int) ($get($base . '.physically_present.value') ?? 0);
                                $considered = (int) ($get($base . '.considered_present.value') ?? 0);
                                $total      = $physical + $considered;

                                $set($base . '.total_present.value', $total);

                                $livewire->validateOnly('mountedActions.0.data.' . $base . '.total_present.value');

                                $livewire->autoSave(
                                    $commitee['id'],
                                    $member['id'],
                                    (int) ($get($base . '.total_meetings.value') ?? 0),
                                    $physical,
                                    $considered,
                                    $total,
                                );
                            }),

                        TextInput::make($base . '.considered_present.value')
                            ->label('Considered Present')
                            ->minValue(0)
                            ->default(0)
                            ->live(onBlur: true)
                            ->extraInputAttributes([
                                'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1').replace('.','')"
                            ])
                            ->afterStateUpdated(function (Get $get, Set $set, $livewire) use ($base, $commitee, $member) {
                                $physical   = (int) ($get($base . '.physically_present.value') ?? 0);
                                $considered = (int) ($get($base . '.considered_present.value') ?? 0);
                                $total      = $physical + $considered;

                                $set($base . '.total_present.value', $total);

                                $livewire->validateOnly('mountedActions.0.data.' . $base . '.total_present.value');

                                $livewire->autoSave(
                                    $commitee['id'],
                                    $member['id'],
                                    (int) ($get($base . '.total_meetings.value') ?? 0),
                                    $physical,
                                    $considered,
                                    $total,
                                );
                            }),

                        TextInput::make($base . '.total_present.value')
                            ->label('Total Number of Attendance')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->dehydrated(true)
                            ->rules([
                                function (Get $get) use ($base) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $base) {
                                        $totalMeetings = (int)$get($base . '.total_meetings.value');
                                        $physical = (int)$get($base . '.physically_present.value');
                                        $considered = (int)$get($base . '.considered_present.value');
                                        $totalPresent = $physical + $considered;

                                        if ($totalPresent > $totalMeetings) {
                                            $fail('Total number of attendance cannot exceed total meetings.');
                                        }
                                        if ($physical > $totalMeetings) {
                                            $fail('Physically present cannot exceed total meetings.');
                                        }
                                        if ($considered > $totalMeetings) {
                                            $fail('Considered present cannot exceed total meetings.');
                                        }
                                    };
                                }
                            ]),

                    ]);
            }

            $tabs[] = Tab::make($commitee['name'])
                ->schema($sections);
        }

        return [
            Tabs::make('Tabs')->tabs($tabs)
        ];
    }
}
