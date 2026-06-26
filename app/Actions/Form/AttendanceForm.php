<?php

namespace App\Actions\Form;

use App\Models\Committee;
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
        $excluded_roles = ['super admin', 'secretariat'];
        $committees = [];

        // Get all unique board members from assignments (excluding Super Admin and Secretariat)
        $board_members = $evaluation_period->assignments()
            ->with('evaluator')
            ->get()
            ->filter(function($assignment) use ($excluded_roles) {
                $role = $assignment->evaluator->roles->first()?->name ?? 'Other';
                return !in_array(strtolower($role), $excluded_roles);
            })
            ->unique('evaluator_id')
            ->keyBy('evaluator_id');

        // 1. Create BOT Meetings tab (default for all board members)
        $bot_members = [];
        foreach($board_members as $member) {
            $bot_members[] = [
                'id' => $member->evaluator_id,
                'name' => $member->evaluator->full_name,
                'role' => $member->evaluator->roles->first()?->name ?? 'Member',
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

        // 2. Create tabs for each committee
        $all_committees = Committee::with('committee_has_trustees.user')->get();

        foreach($all_committees as $committee) {
            $committee_members = [];

            foreach($committee->committee_has_trustees as $trustee) {
                // Only include members who are in the board_members list
                if (isset($board_members[$trustee->user_id])) {
                    $committee_members[] = [
                        'id' => $trustee->user_id,
                        'name' => $trustee->user->full_name,
                        'role' => $trustee->user->roles->first()?->name ?? 'Member',
                        'committee_id' => $committee->id,
                        'total_meetings' => 0,
                        'physically_present' => 0,
                        'considered_present' => 0,
                        'total_present' => 0,
                        'attendance_rating_scale_values_id' => 0,
                    ];
                }
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
