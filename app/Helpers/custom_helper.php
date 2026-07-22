<?php

use App\Models\CommitteeHasTrustee;
use App\Models\EvaluationForm;
use App\Models\EvaluationFormSection;
use Spatie\Permission\Models\Role;

if (! function_exists('get_eval_form_by_role')) {
    function get_eval_form_by_role($role)
    {

        $form = null;

        $model = new EvaluationForm();

        switch(strtolower($role)){
            case 'chairman':
                $form =  $model->find(1);
                break;
            case 'vice chairman':
                $form =  $model->find(2);
                break;

            case 'trustee':
                $form =  $model->find(2);
                break;

            case 'evp-gm':
                $form =  $model->find(3);
                break;

            case 'corporate secretary':
                $form =  $model->find(4);
                break;


            case 'corporate treasurer':
                $form =  $model->find(5);
                break;

            case 'treasurer':
                $form =  $model->find(5);
                break;

            case 'corporate comptroller':
                $form =  $model->find(6);
                break;

            case 'comptroller':
                $form =  $model->find(6);
                break;

            case 'lead resource person':
                $form =  $model->find(7);
                break;


            default:
                $form =  null;
                break;

        }
        if(!$form){
            return $form;
        }
        return $form->id;

    }
}
if (! function_exists('check_committee_permission')) {
    function check_committee_permission($committee_id,$permission)
    {
        if(auth()->user()->hasPermissionTo('FullAccess:Committee')) return true;

        $user_id = auth()->user()->id;
        $role_id = CommitteeHasTrustee::where('committee_id', $committee_id)
            ->where('user_id', $user_id)
            ->first()?->role_id;

        $role = Role::where('id', $role_id)->first();
        return $role->hasPermissionTo($permission);

    }
}
if (! function_exists('check_eval_form_sections')) {
    function check_eval_form_sections($ef_id,$section_type_id)
    {
        return EvaluationFormSection::where('evaluation_form_id',$ef_id)
        ->where('section_type_id',$section_type_id)
        ->first();
    }
}

if (! function_exists('get_executive_role')) {
    function get_executive_role($role = null)
    {
        if(!$role){
               $role =  auth()->user()->roles->first()->name;
        }
        $executive_roles = ['super admin', 'secretariat'];

        if(in_array(strtolower($role), $executive_roles)){
            return true;
        }
        return false;
    }
}



if (! function_exists('get_board_members')) {
    function get_board_members()
    {
        $board_members = ['Chairman', 'Trustee', 'Vice Chairman'];

        return $board_members;
    }
}

if (! function_exists('get_available_forms_for_evaluation')) {
    function get_available_forms_for_evaluation($evaluator_id, $evaluatee_id)
    {
        $evaluator = \App\Models\User::find($evaluator_id);
        $evaluatee = \App\Models\User::find($evaluatee_id);

        if (!$evaluator || !$evaluatee) {
            return [];
        }

        $evaluatorRoles = $evaluator->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
        $evaluateeRoles = $evaluatee->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();

        // Check if evaluator is governance (has board roles or executive roles)
        $boardRoles = ['chairman', 'vice chairman', 'trustee'];
        $isEvaluatorGovernance = !empty(array_intersect($evaluatorRoles, $boardRoles));

        $availableForms = [];

        // Handle board hierarchy: Chairman > Vice Chairman > Trustee (only highest level is evaluated)
        $boardHierarchy = ['chairman', 'vice chairman', 'trustee'];
        $evaluateeHasBoard = false;
        $highestBoardRole = null;

        foreach ($boardHierarchy as $role) {
            if (in_array($role, $evaluateeRoles)) {
                $evaluateeHasBoard = true;
                $highestBoardRole = $role;
                break; // Take the highest in hierarchy
            }
        }

        // Governance members evaluate board members and other roles
        if ($isEvaluatorGovernance) {
            // If evaluatee has a board role, only evaluate that one (not duplicates)
            if ($highestBoardRole) {
                $formId = get_eval_form_by_role($highestBoardRole);
                if ($formId) {
                    $availableForms[] = $formId;
                }
            } else {
                // Evaluate other governance roles (Corporate Secretary, Treasurer, Comptroller, EVP-GM)
                foreach (['corporate secretary', 'treasurer', 'comptroller', 'evp-gm'] as $role) {
                    if (in_array($role, $evaluateeRoles)) {
                        $formId = get_eval_form_by_role($role);
                        if ($formId) {
                            $availableForms[] = $formId;
                        }
                    }
                }
            }
        }

        // C7 - Lead Resource Person (both governance and non-governance, but same committee only)
        if (in_array('lead resource person', $evaluateeRoles)) {
            $evaluateeCommittee = CommitteeHasTrustee::where('user_id', $evaluatee_id)
                ->whereHas('role', fn($q) => $q->where('name', 'Lead Resource Person'))
                ->first()?->committee_id;

            if ($evaluateeCommittee) {
                // Check if evaluator is in the same committee
                $evaluatorInSameCommittee = CommitteeHasTrustee::where('user_id', $evaluator_id)
                    ->where('committee_id', $evaluateeCommittee)
                    ->exists();

                if ($evaluatorInSameCommittee) {
                    $availableForms[] = 7;
                }
            }
        }

        // Return unique form IDs, sorted
        return array_unique(array_values($availableForms));
    }
}
