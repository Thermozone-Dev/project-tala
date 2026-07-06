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

        // Get all assignments (evaluators) for this evaluation period
        $assignments = $evaluation_period->assignments()
            ->with('evaluator.roles')
            ->get();

        // Get all trustee evaluations (members being evaluated)
        $trustee_evaluations = TrusteeHasEvaluation::where('evaluation_id', $evaluation_period->id)
            ->with('member.roles')
            ->get();

        // 1. Create BOT Meetings tab - Combine BOT evaluators and members (deduplicated by user id)
        $bot_evaluators = $assignments->whereNull('committee_id');
        $bot_members_being_evaluated = $trustee_evaluations->whereNull('committee_id');

        $bot_user_ids = [];
        $bot_members = [];

        // Add evaluators
        foreach($bot_evaluators as $assignment) {
            $user = $assignment->evaluator;
            if(!$user || in_array($user->id, $bot_user_ids)){
                continue;
            }
            $role = $user?->roles?->first()?->name ?? 'Member';

            // Exclude Lead Resource Persons from BOT meetings
            if (strtolower($role) === 'lead resource person') {
                continue;
            }

            $bot_user_ids[] = $user->id;

            $bot_members[] = [
                'id' => $user->id,
                'name' => $user->full_name,
                'role' => $role,
                'committee_id' => null,
                'total_meetings' => 0,
                'physically_present' => 0,
                'considered_present' => 0,
                'total_present' => 0,
                'attendance_rating_scale_values_id' => 0,
            ];
        }

        // Add members being evaluated (if not already added as evaluator)
        foreach($bot_members_being_evaluated as $evaluation) {
            $user = $evaluation->member;
            if(!$user || in_array($user->id, $bot_user_ids)){
                continue;
            }
            $role = $user?->roles?->first()?->name ?? 'Member';

            // Exclude Lead Resource Persons from BOT meetings
            if (strtolower($role) === 'lead resource person') {
                continue;
            }

            $bot_user_ids[] = $user->id;

            $bot_members[] = [
                'id' => $user->id,
                'name' => $user->full_name,
                'role' => $role,
                'committee_id' => null,
                'total_meetings' => 0,
                'physically_present' => 0,
                'considered_present' => 0,
                'total_present' => 0,
                'attendance_rating_scale_values_id' => 0,
            ];
        }

        // Add SVP-Operations from committee evaluations to BOT tab
        $committee_evaluations = $trustee_evaluations->whereNotNull('committee_id');
        foreach($committee_evaluations as $evaluation) {
            $user = $evaluation->member;
            if(!$user || in_array($user->id, $bot_user_ids)){
                continue;
            }
            $role = $user?->roles?->first()?->name ?? 'Member';

            // Only add SVP-Operations to BOT tab
            if (strtolower($role) !== 'svp-operation') {
                continue;
            }

            $bot_user_ids[] = $user->id;

            $bot_members[] = [
                'id' => $user->id,
                'name' => $user->full_name,
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

        // 2. Create tabs for each committee - Combine committee evaluators and members (deduplicated by user id)
        $all_committees = Committee::get();

        foreach($all_committees as $committee) {
            $committee_evaluators = $assignments->where('committee_id', $committee->id);
            $committee_members_being_evaluated = $trustee_evaluations->where('committee_id', $committee->id);

            $committee_user_ids = [];
            $committee_members = [];

            // Add evaluators
            foreach($committee_evaluators as $assignment) {
                $user = $assignment->evaluator;
                if(!$user || in_array($user->id, $committee_user_ids)){
                    continue;
                }
                $committee_user_ids[] = $user->id;
                $role = $user->roles->first()?->name ?? 'Member';

                $committee_members[] = [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'role' => $role,
                    'committee_id' => $committee->id,
                    'total_meetings' => 0,
                    'physically_present' => 0,
                    'considered_present' => 0,
                    'total_present' => 0,
                    'attendance_rating_scale_values_id' => 0,
                ];
            }

            // Add members being evaluated (if not already added as evaluator)
            foreach($committee_members_being_evaluated as $evaluation) {
                $user = $evaluation->member;
                if(!$user || in_array($user->id, $committee_user_ids)){
                    continue;
                }
                $committee_user_ids[] = $user->id;
                $role = $user->roles->first()?->name ?? 'Member';

                $committee_members[] = [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'role' => $role,
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
