<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Filament\Notifications\Notification;

class EvaluationFormPDF extends Controller
{
    public $evaluation;
    public $attendance_rating;
    public $assesment_rating;
    public $pdfData;
    public $eval_result;

    public function getEvaluationResult(Request $request){

        $formId = request()->query('formID', null);

        $evaluation = \App\Models\EvaluationForm::find($formId);
        $trustee_evaluation = null;
        if($request->trustee_evaluation_id){
            $trustee_evaluation = \App\Models\TrusteeHasEvaluation::find($request->trustee_evaluation_id);
            $evaluation = $trustee_evaluation->form;
            // dd($trustee_evaluation,$trustee_evaluation->);
             if(!$trustee_evaluation){
                Notification::make()
                    ->title('Trustee evaluation not found.')
                    ->danger()
                    ->send();

                    return redirect()->route('filament.admin.pages.dashboard');
             }
        }

        if(!$evaluation){
            Notification::make()
                ->title('Evaluation form not found.')
                ->danger()
                ->send();
                return redirect()->route('filament.admin.pages.dashboard');

        }

        $commitees = Committee::all();

        $this->evaluation = $evaluation;
        $this->eval_result = $trustee_evaluation ?? null;

        $this->assesment_rating = null;
        $this->attendance_rating = null;


        $header_data = [
            'name' => ($trustee_evaluation) ? $trustee_evaluation?->member?->full_name ?? null : "Juan Dela Cruz (Preview)",
            'commitees' => $commitees->map(function ($item) use ($trustee_evaluation){
                $is_member = false;
                if($trustee_evaluation){
                    $is_member = ($item->committee_has_trustees->where('user_id', $trustee_evaluation->member_id)->first()) ? true : false;
                }
                return [
                    'name' => $item->name,
                    'is_member' => $is_member,
                ];
            })->chunk(3),
            'coverage_period' => ($trustee_evaluation) ? $trustee_evaluation->evaluationPeriod->formatted_coverage : "-",
            'evaluated_by' => ($trustee_evaluation) ? $trustee_evaluation->evaluator->getFullNameAttribute() : "John Doe (Preview)",
        ];
        $this->pdfData = [
            'title' => $evaluation->title,
            'show_instruction' => true,
            'date' => date('m/d/Y'),
            'sections' => $this->getData(), //dont remove-move need to gather rating typr
            'assesment_rating' => $this->assesment_rating ?? null,
            'attendance_rating' => $this->attendance_rating ?? null,
            'header_data' => $header_data,
        ];

        switch( $evaluation->id){
            case 1:
                return $this->evaluation_c1();
                break;
            case 2:
                return $this->evaluation_c2();
                break;
            case 3:
                return $this->evaluation_c3();
                break;
            case 4:
                return $this->evaluation_c4();
                break;
            case 5:
                return $this->evaluation_c5();
                break;
            case 6:
                return $this->evaluation_c6();
                break;
            case 7:
                return $this->evaluation_c7();
                break;
            case 8:
                return $this->committee_selfassement();
                break;
            case 9:
                return $this->bot_selfassement();
                break;
            default:
                return $this->evaluation_c1();
        }
        return $this->committee_selfassement();
    }

    public function evaluation_c1(){

        $data = $this->pdfData;
        $filename = 'evaluation_result_c1';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c2(){
        $data = $this->pdfData;
        $filename = 'evaluation_result_c2';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c3(){
        $data = $this->pdfData;
        $filename = 'evaluation_result_c3';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c4(){
        $data = $this->pdfData;
        $filename = 'evaluation_result_c4';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c5(){
        $data = $this->pdfData;
        $filename = 'evaluation_result_c5';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c6(){
        $data = $this->pdfData;
        $filename = 'evaluation_result_c6';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c7(){
        $data = $this->pdfData;
        $filename = 'evaluation_result_c6';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function bot_selfassement(){
        $data = $this->pdfData;
        $data['show_bot_self_instruction'] = true;
        $data['period_covered'] = ($this->eval_result) ? $this->eval_result->evaluationPeriod->formatted_coverage : null;
        $data['committee'] = ($this->eval_result) ? $this->eval_result?->committee?->name ?? null : null;
        $filename = 'BOT Self Assesment Questionaire';
        return $this->exportPDF('pdf.evaluation_results.selfassesment', $data, $filename);
    }

    public function committee_selfassement(){
        $data = $this->pdfData;
        $data['period_covered'] = ($this->eval_result) ? $this->eval_result->evaluationPeriod->formatted_coverage : null;
        $data['show_bot_self_instruction'] = true;
        $filename = 'BOARD Self Assesment Questionaire';
        return $this->exportPDF('pdf.evaluation_results.selfassesment', $data, $filename);
    }


    public function getData(){
        $evaluation = $this->evaluation;
        $sections = [];
        foreach($evaluation->sections as $eval_sec){
            $sec_data = [
                'section_type' => $eval_sec->section_type_id, //assesment
                'title' => $eval_sec->title,
                'add_remarks' => $eval_sec->add_remarks,
            ];

            if($eval_sec->section_type_id == 1){ //assesment
                $assesment_answer = $this->eval_result ? $this->eval_result->assesment_answer : collect();
                $questions = $eval_sec->questionnaires->sortBy('id')->map(function ($item) use ($assesment_answer) {
                    $answer = $assesment_answer->where('questionnaire_id',$item->id)->first();
                    return [
                        'name' => $item->name,
                        'answer' => $answer?->ratingScaleValue?->value ?? null,
                        'remarks' => $answer?->remarks ?? null
                    ];
                });

                $sec_data['questions'] = $questions;
            }

            if($eval_sec->section_type_id == 2){ //attendance
                $attendance_answer = $this->eval_result ? $this->eval_result->attendance_answer : collect();
                $attendance = $eval_sec->attendanceSection;

                $attendance_criteria = [
                    'show_total_meetings' => [
                        'name' => 'Total Number of meetings',
                        'show' => $attendance->show_total_meetings
                    ],
                    'show_physically_present' => [
                        'name' => 'Physically Present ',
                        'show' => $attendance->show_physically_present
                    ],
                    'show_considered_present' => [
                        'name' => 'Considered as present',
                        'show' => $attendance->show_considered_present
                    ],
                    'show_total_present' => [
                        'name' => 'Total Meetings Present',
                        'show' => $attendance->show_total_present
                    ],
                    'show_attendance_rating' => [
                        'name' => 'Attendance Rating',
                        'show' => $attendance->show_attendance_rating
                    ],
                ];
                $meetings =   $attendance->meetings->map(function ($item) use ($attendance_answer, $attendance){
                    $answer = $attendance_answer->where('meeting_id',$item->id)->first();
                    return [
                        'name' => $item->name,
                        'show_total_meetings' => ($attendance->show_total_meetings) ? $answer?->total_meetings ?? null : null,
                        'show_physically_present' => ($attendance->show_physically_present) ? $answer?->physically_present ?? null : null,
                        'show_considered_present' => ($attendance->show_considered_present) ? $answer?->considered_present ?? null : null,
                        'show_total_present' => ($attendance->show_total_present) ? $answer?->total_present ?? null : null,
                        'show_attendance_rating' => ($attendance->show_attendance_rating) ? $answer?->ratingScaleValue?->value ?? null : null,
                    ];
                });
                // dd($meetings);
                $sec_data['attendance'] = ['criteria' => $attendance_criteria, 'meetings' => $meetings];
            }

            if($eval_sec->section_type_id == 3){ // other comments
                $other_comments = $this->eval_result ? $this->eval_result->other_comments->first()?->comment : '';

                $sec_data['other_comments'] = $other_comments;
            }
            if($eval_sec->rating_scale_id == 1 || $eval_sec->rating_scale_id == 3){
                $ass_rating = $eval_sec->ratingScale->values
                                ->map(function ($item) {
                                    return [
                                        'id' => $item->id,
                                        'value' => $item->value,
                                        'qualitative' => $item->qualitative,
                                    ];
                                })
                                ->values();
                $this->assesment_rating = ($eval_sec->rating_scale_id == 1) ? $ass_rating->sortByDesc('value') : $ass_rating->sortBy('value');
            }
            if($eval_sec->rating_scale_id == 2){
                $this->attendance_rating = $eval_sec->ratingScale->values->sortByDesc('value');
            }

            $sections[] = $sec_data;
        }
        return $sections;
    }

    public function exportPDF($path, $data, $filename){

        return PDF::loadView($path, $data)
                    ->setOption('encoding', 'UTF-8')
                    ->setOption('header-html', view('pdf.components.header')->render())
                    // ->setOption('footer-html', view('pdf.footer')->render())
                    ->setOptions(['margin-left' => 5, 'margin-top' => 40, 'margin-right' => 10, 'margin-bottom' => 10])
                    ->setOption('enable-local-file-access', true)
                    ->setOption('images', true)
                    ->stream($filename.'.pdf');

    }

}
