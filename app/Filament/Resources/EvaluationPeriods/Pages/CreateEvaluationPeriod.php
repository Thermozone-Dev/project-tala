<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateEvaluationPeriod extends CreateRecord
{
    protected static string $resource = EvaluationPeriodResource::class;


    public function mount(): void{

        if(EvaluationPeriod::where('status_id', 1)->exists()){
            Notification::make()
                ->title('An active evaluation period already exists.')
                ->body('Please end the current evaluation period before starting a new one.')
                ->danger()
                ->send();

            redirect($this->getResource()::getUrl('index'));
        }
    }


    public function handleRecordCreation(array $data): Model
    {

        $data['created_by'] = auth()->id(); // Set the created_by field to the current user's ID
        $data['status_id'] = 1; // Set the created_by field to the current user's ID

        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        try {
            // Check for governance committee
            $governance_committee = $this->getGovernanceCommittee();
            if (!$governance_committee) {
                Notification::make()
                    ->title('Error')
                    ->body('No governance committee found. Please ensure a committee with "Governance" in its name exists.')
                    ->danger()
                    ->send();

                $this->record->delete();
                return;
            }

            DB::transaction(function () use ($governance_committee) {
                // 1. Form 8 (Self Assessment) for all board members
                $board_members = User::role(get_board_members())->get();
                foreach ($board_members as $member) {
                    $this->record->assignments()->create([
                        'ef_id' => 8,
                        'committee_id' => null,
                        'member_id' => null,
                        'evaluator_id' => $member->id,
                    ]);
                }

                // 2. Committee assignments: Form 9 for board members only + Form 7 (C7) for evaluating LRPs
                $board_member_roles = array_map('strtolower', get_board_members());
                $committees = Committee::with('committee_has_trustees.role', 'committee_has_trustees.user.roles')->get();
                foreach ($committees as $committee) {
                    // Only get board member evaluators using get_board_members()
                    $board_member_evaluators = $committee->committee_has_trustees
                        ->filter(function($member) use ($board_member_roles) {
                            $role_name = strtolower($member->role?->name ?? $member->user?->roles->first()?->name ?? '');
                            return in_array($role_name, $board_member_roles);
                        })
                        ->unique('user_id');

                    // Find all LRPs and SVP-Operations in this committee (roles only evaluated within their committees)
                    $committee_only_roles = ['lead resource person', 'svp-operation', 'svp administration'];
                    $committee_only_members = $committee->committee_has_trustees
                        ->filter(fn($member) => in_array(strtolower($member->role->name), $committee_only_roles));

                    foreach ($board_member_evaluators as $evaluator) {
                        // Form 9: Committee Self Assessment for all members
                        $this->record->assignments()->create([
                            'ef_id' => 9,
                            'committee_id' => $committee->id,
                            'member_id' => null,
                            'evaluator_id' => $evaluator->user_id,
                        ]);

                        // Form 7 (C7): Each member evaluates all committee-only members (LRPs, SVP-Operations) in the committee
                        foreach ($committee_only_members as $member) {
                            // Don't create self-evaluation
                            if ($evaluator->user_id === $member->user_id) {
                                continue;
                            }

                            $this->record->assignments()->create([
                                'ef_id' => 7,
                                'committee_id' => $committee->id,
                                'member_id' => $member->user_id,
                                'evaluator_id' => $evaluator->user_id,
                            ]);
                        }
                    }
                }

                // 3. Cross-evaluation: Board members evaluate all non-executive roles
                // Define role priority (higher index = higher priority)
                $role_priority = [
                    'trustee' => 1,
                    'corporate officer' => 2,
                    'treasurer' => 3,
                    'comptroller' => 4,
                    'svp-operation' => 6,
                    'evp-gm' => 7,
                    'corporate secretary' => 8,
                    'lead resource person' => 9,
                    'vice chairman' => 10,
                    'chairman' => 11,
                ];

                $board_members = User::role(get_board_members())
                    ->with('roles')
                    ->get();

                // Get all users with non-executive roles (excluding super admin and secretariat)
                $executive_roles = ['super admin', 'secretariat'];
                $all_users = User::with('roles')->get();

                // Get governance committee members
                $governance_members = $governance_committee->committee_has_trustees()
                    ->with('user')
                    ->pluck('user_id')
                    ->toArray();

                foreach ($board_members as $evaluator) {
                    $is_in_governance = in_array($evaluator->getKey(), $governance_members);

                    foreach ($all_users as $evaluatee) {
                        // Don't create self-evaluation
                        if ($evaluator->getKey() === $evaluatee->getKey()) {
                            continue;
                        }

                        // Get all non-executive roles for the evaluatee
                        $evaluatee_roles = $evaluatee->roles()
                            ->whereNotIn('name', $executive_roles)
                            ->get();

                        // Skip if evaluatee has no non-executive roles
                        if ($evaluatee_roles->isEmpty()) {
                            continue;
                        }

                        // Get the highest priority role
                        $highest_priority_role = null;
                        $highest_priority_value = -1;

                        foreach ($evaluatee_roles as $role) {
                            $priority = $role_priority[strtolower($role->name)] ?? 0;
                            if ($priority > $highest_priority_value) {
                                $highest_priority_value = $priority;
                                $highest_priority_role = $role;
                            }
                        }

                        if (!$highest_priority_role) {
                            continue;
                        }

                        // Check if evaluator can evaluate this person
                        $can_evaluate = false;

                        if ($is_in_governance) {
                            // Governance members can evaluate all non-executive board members
                            $can_evaluate = true;
                        } else {
                            // Non-governance members can only evaluate LRPs in their committees
                            if (strtolower($highest_priority_role->name) === 'lead resource person') {
                                $evaluator_committees = $evaluator->committees()
                                    ->pluck('committee_id')
                                    ->toArray();

                                $evaluatee_committees = $evaluatee->committees()
                                    ->pluck('committee_id')
                                    ->toArray();

                                // Check if they share any committee
                                $can_evaluate = count(array_intersect($evaluator_committees, $evaluatee_committees)) > 0;
                            }
                        }

                        if ($can_evaluate) {
                            $eval_form = get_eval_form_by_role($highest_priority_role->name);
                            if ($eval_form) {
                                // Check if assignment already exists to avoid duplicates
                                $exists = $this->record->assignments()
                                    ->where('evaluator_id', $evaluator->getKey())
                                    ->where('member_id', $evaluatee->getKey())
                                    ->where('ef_id', $eval_form)
                                    ->exists();

                                if (!$exists) {
                                    $this->record->assignments()->create([
                                        'ef_id' => $eval_form,
                                        'committee_id' => null,
                                        'member_id' => $evaluatee->getKey(),
                                        'evaluator_id' => $evaluator->getKey(),
                                    ]);
                                }
                            }
                        }
                    }
                }
            });

            Notification::make()
                ->title('Success')
                ->body('Evaluation period created and all assignments delegated successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to create evaluation period: ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    private function getGovernanceCommittee(): ?Committee
    {
        // First try ID 2 (default)
        $committee = Committee::find(2);
        if ($committee && stripos($committee->name, 'governance') !== false) {
            return $committee;
        }

        // If ID 2 doesn't work, search by name
        $committee = Committee::where('name', 'like', '%Governance%')->first();
        if ($committee) {
            return $committee;
        }

        // Not found
        return null;
    }
}
