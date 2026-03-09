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
                $form =  $model->find(3);
                break;

            case 'trustee':
                $form =  $model->find(2);
                break;

            case 'corporate officer':
                $form =  $model->find(4);
                break;

            case 'lead resource person':
                $form =  $model->find(7);
                break;

            default:
                $form =  null;
                break;

        }
        if(!$form){
            dd('No form found for role: ' . $role);
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
