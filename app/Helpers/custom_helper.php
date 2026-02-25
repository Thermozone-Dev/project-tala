<?php

use App\Models\EvaluationForm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                $form =  $model->find(3);
                break;

            case 'corporate officer':
                $form =  $model->find(4);
                break;

            case 'lead resource person':
                $form =  $model->find(5);
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