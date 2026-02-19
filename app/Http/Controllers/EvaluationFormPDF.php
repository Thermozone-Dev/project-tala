<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;


class EvaluationFormPDF extends Controller
{

    public function getEvaluationResult(){
        // return $this->evaluation_c1();
        // return $this->evaluation_c2();
        // return $this->evaluation_c3();
        // return $this->evaluation_c4();
        // return $this->evaluation_c5();
        // return $this->evaluation_c6();
        // return $this->evaluation_c7();
        // return $this->bot_selfassement();
        return $this->committee_selfassement();

    }

    public function evaluation_c1(){
        $data = [
            'title' => 'BOT EVALUATION FORM C.1 - CHAIRMAN OF THE BOARD',
            'show_instruction' => true,
            'date' => date('m/d/Y'),
            'sections' => $this->getData(),
        ];
        $filename = 'evaluation_result_c1';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c2(){
        $data = [
            'title' => 'E BOT EVALUATION FORM C.2 - TRUSTEES',
            'date' => date('m/d/Y'),
            'show_instruction' => true,
            'sections' => $this->getData(),
        ];
        $filename = 'evaluation_result_c2';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c3(){
        $data = [
            'title' => "EBOT EVALUATION FORM C.3 - EVP – GM",
            'date' => date('m/d/Y'),
            'show_instruction' => true,
            'sections' => $this->getData(),
        ];
        $filename = 'evaluation_result_c3';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c4(){
        $data = [
            'title' => "BOT EVALUATION FORM C.4 - Corporate Secretary",
            'date' => date('m/d/Y'),
            'show_instruction' => true,
            'sections' => $this->getData(),
        ];
        $filename = 'evaluation_result_c4';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c5(){
        $data = [
            'title' => "BOT EVALUATION FORM C.5 - Corporate Treasurer",
            'date' => date('m/d/Y'),
            'show_instruction' => true,
            'sections' => $this->getData(),
        ];
        $filename = 'evaluation_result_c5';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c6(){
        $data = [
            'title' => "BOT EVALUATION FORM C.6 - Corporate Comptroller",
            'date' => date('m/d/Y'),
            'show_instruction' => true,
            'sections' => $this->getData(),
        ];
        $filename = 'evaluation_result_c6';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function evaluation_c7(){
        $data = [
            'title' => "BOT EVALUATION FORM C.7 – LEAD RESOURCE PERSONS (LRPs)",
            'date' => date('m/d/Y'),
            'show_instruction' => false,
            'sections' => $this->getData(),
        ];
        $filename = 'evaluation_result_c6';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
    }

    public function bot_selfassement(){
        $data = [
            'title' => "BOT COMMITTEE SELF-ASSESSMENT QUESTIONNAIRE",
            'period_covered' => "June 2025 to April 2026",
            'show_bot_self_instruction' => true,
            'sections' => $this->getData(),
        ];
        $filename = 'BOT Self Assesment Questionaire';
        return $this->exportPDF('pdf.evaluation_results.selfassesment', $data, $filename);
    }

    public function committee_selfassement(){
        $data = [
            'title' => "BOT COMMITTEE SELF-ASSESSMENT QUESTIONNAIRE",
            'period_covered' => "June 2025 to April 2026",
            'committee' => "AUDIT AND COMPLIANCE COMMITTEE",
            'show_committee_self_instruction' => true,
            'sections' => $this->getData(),
        ];
        $filename = 'BOT Self Assesment Questionaire';
        return $this->exportPDF('pdf.evaluation_results.selfassesment', $data, $filename);
    }


    public function getData(){
        $attendance_criteria = [
            'Total Number of meetings',
            'Physically Present',
            'Considered as present',
            'Total Meetings Present',
            'Attendance Rating',
        ];

        $meetings_to_asses = [
            'BOT Meetings(Special & Regular)',
            'Audit & Compliance',
            'Human Resource',
        ];

        $questions = [
            '1. Ability to control and regulate BOT meetings.',
            '2. Ability to control and regulate BOT meetings.',
            '3. Ability to control and regulate BOT meetings.',
            '4. Ability to control and regulate BOT meetings.',
            '5. Ability to control and regulate BOT meetings.',
            '6. Ability to control and regulate BOT meetings.',
            '7. Ability to control and regulate BOT meetings.',
            '8. Ability to control and regulate BOT meetings.',
            '9. Ability to control and regulate BOT meetings.',
            '10. Ability to control and regulate BOT meetings.',
        ];

        $sections = [
            [
                'section_type' => 1, //assesment
                'title' => 'A.  Performance of Role as Corporate Officer - 70 % ',
                'questions' => $questions,
                'add_remarks' => true,
            ],
            [
                'section_type' => 1, //assesment
                'title' => 'B.  Performance of Role as Corporate Officer - 70 % ',
                'questions' => $questions,
                'add_remarks' => true,
            ],
            [
                'section_type' => 1, //assesment
                'title' => 'C.  Performance of Role as Corporate Officer - 70 % ',
                'questions' => $questions,
                'add_remarks' => true,
            ],
            // [
            //     'section_type' => 2, //attendance
            //     'title' => 'B.  Attendance in BOT / Committee Meetings & other related activities (to be rated by the Corporate Secretary) -30%',
            //     'attendance' => ['criteria' => $attendance_criteria, 'meetings' => $meetings_to_asses],
            //     'rating_scale' => $this->getAttendanceRatingScale(),
            //     'add_remarks' => true,
            // ],
        ];
        return $sections;
    }

    public function getAttendanceRatingScale(){
        $rating_scale = [
            [
                'name' => '100% attendance',
                'quantitative' => 5,
                'qualitative' => 'Excellent',
            ],
            [
                'name' => '80% to less than 100% attendance',
                'quantitative' => 4,
                'qualitative' => 'Superior',
            ],
            [
                'name' => '60% to less than 80% attendance',
                'quantitative' => 3,
                'qualitative' => 'Very Good',
            ],
            [
                'name' => '40% to less than 60% attendance',
                'quantitative' => 2,
                'qualitative' => 'Good',
            ],
            [
                'name' => 'Less than 40% of the time',
                'quantitative' => 1,
                'qualitative' => 'Satisfactory',
            ],
        ];


        return collect($rating_scale);
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


    //
}
