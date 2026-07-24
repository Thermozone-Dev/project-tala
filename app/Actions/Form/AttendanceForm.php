<?php

namespace App\Actions\Form;

use App\Models\AttendanceAnswer;
use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use Filament\Forms\Components\Select;
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

    /**
     * Sort members by role hierarchy: Trustees > Corporate Officers > LRPs
     * Also groups by committee for easier navigation
     */
    private function sortMembersByRoleHierarchy($members)
    {
        $roleHierarchy = [
            'chairman' => 1,
            'vice chairman' => 2,
            'trustee' => 3,
            'treasurer' => 4,
            'comptroller' => 4,
            'corporate secretary' => 4,
            'evp-gm' => 4,
            'svp-operation' => 4,
            'svp administration' => 4,
            'lead resource person' => 5,
        ];

        usort($members, function ($a, $b) use ($roleHierarchy) {
            $roleA = strtolower($a['role'] ?? '');
            $roleB = strtolower($b['role'] ?? '');

            $orderA = $roleHierarchy[$roleA] ?? 99;
            $orderB = $roleHierarchy[$roleB] ?? 99;

            // Sort by role hierarchy first
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }

            // Then sort alphabetically by name within same role
            return strcasecmp($a['name'], $b['name']);
        });

        return $members;
    }

    public function handle($evaluation_period = null, $trustees = [])
    {
        if (!$evaluation_period) {
            return [];
        }

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

            // Exclude only pure LRPs (users with ONLY LRP role)
            $user_roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            $executive_roles = ['super admin', 'secretariat'];

            if (count($user_roles) === 1 && in_array('lead resource person', $user_roles)) {
                continue; // Exclude pure LRPs only
            }

            if (in_array(strtolower($user_roles[0] ?? ''), $executive_roles)) {
                continue; // Exclude executives
            }

            $bot_user_ids[] = $user->id;
            $role = $user?->roles?->first()?->name ?? 'Member';

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

            // Exclude only pure LRPs (users with ONLY LRP role)
            $user_roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            $executive_roles = ['super admin', 'secretariat'];

            if (count($user_roles) === 1 && in_array('lead resource person', $user_roles)) {
                continue; // Exclude pure LRPs only
            }

            if (in_array(strtolower($user_roles[0] ?? ''), $executive_roles)) {
                continue; // Exclude executives
            }

            $bot_user_ids[] = $user->id;
            $role = $user?->roles?->first()?->name ?? 'Member';

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

        // Add all users with SVP-Operations or SVP Administration roles from committees to BOT tab
        $svp_users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['SVP-Operation', 'SVP Administration']);
        })
        ->with('roles')
        ->get();

        foreach($svp_users as $user) {
            if(in_array($user->id, $bot_user_ids)){
                continue;
            }

            $bot_user_ids[] = $user->id;
            $role = $user->roles->first()?->name ?? 'Member';

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

        // Add manually added attendees from AttendanceAnswer for BOT
        $manual_attendances = AttendanceAnswer::where('evaluation_period_id', $evaluation_period->id)
            ->whereNull('committee_id')
            ->with('trustee.roles')
            ->get();

        foreach($manual_attendances as $attendance) {
            $user = $attendance->trustee;
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

        if (!empty($bot_members)) {
            // Sort BOT members by role hierarchy
            $bot_members = $this->sortMembersByRoleHierarchy($bot_members);

            $committees[] = [
                'id' => 'bot',
                'name' => 'BOT Meetings',
                'members' => $bot_members
            ];
        }

        // 2. Create tabs for each committee - Combine committee evaluators and members (deduplicated by user id)
        $all_committees = Committee::get();

        foreach($all_committees as $committee) {
            $committee_user_ids = [];
            $committee_members = [];

            // Get all members directly assigned to this committee (1 member per committee, no duplication)
            $committee_has_trustees = \App\Models\CommitteeHasTrustee::where('committee_id', $committee->id)
                ->with('user.roles')
                ->get();

            foreach($committee_has_trustees as $trustee) {
                $user = $trustee->user;
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

            // Add manually added attendees from AttendanceAnswer for this committee (if not already added)
            $manual_committee_attendances = AttendanceAnswer::where('evaluation_period_id', $evaluation_period->id)
                ->where('committee_id', $committee->id)
                ->with('trustee.roles')
                ->get();

            foreach($manual_committee_attendances as $attendance) {
                $user = $attendance->trustee;
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
                // Sort committee members by role hierarchy
                $committee_members = $this->sortMembersByRoleHierarchy($committee_members);

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

    public static function getAddAttendeeSchema(): array
    {
        return [
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

                    // For BOT: Show all users except LRPs and executives
                    if ($committee_id === 'bot') {
                        return User::whereHas('roles', function ($query) use ($executive_roles) {
                            $query->whereNotIn('name', array_merge($executive_roles, ['lead resource person']));
                        })
                        ->orWhereDoesntHave('roles')
                        ->get()
                        ->mapWithKeys(fn($user) => [$user->id => $user->full_name])
                        ->toArray();
                    }

                    // For any committee: Show all users except executives (no committee filtering)
                    return User::whereHas('roles', function ($query) use ($executive_roles) {
                        $query->whereNotIn('name', $executive_roles);
                    })
                    ->orWhereDoesntHave('roles')
                    ->get()
                    ->mapWithKeys(fn($user) => [$user->id => $user->full_name])
                    ->toArray();
                })
                ->searchable([
                    'first_name',
                    'last_name',
                    'middle_name'
                ])
                ->required()
                ->reactive(),
        ];
    }

    public static function handleAddAttendeeAction(array $data, $evaluation_period_id): array
    {
        $evaluationPeriod = EvaluationPeriod::find($evaluation_period_id);
        $committee_id = $data['committee_id'] === 'bot' ? null : (int) $data['committee_id'];
        $trustee_id = (int) $data['trustee_id'];

        // Validate: Check if this trustee already has attendance for this committee
        $existingAttendance = AttendanceAnswer::where('trustee_id', $trustee_id)
            ->where('evaluation_period_id', $evaluationPeriod->id)
            ->where('committee_id', $committee_id)
            ->exists();

        if ($existingAttendance) {
            return [
                'success' => false,
                'message' => 'This attendee already has an attendance record for this meeting type.'
            ];
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

        return [
            'success' => true,
            'message' => 'Attendee has been successfully added.'
        ];
    }

}
