<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;


class EvaluationFormPDF extends Controller
{

    public function getEvaluationResult(){
        return $this->evaluation_c1();
    }

    public function evaluation_c1(){

        $attendance_criteria = [
            'Total Number of meetings',
            'Physically Present',
            'Considered as present',
            'Total Meetings Present',
            'Attendance Rating',
        ];

        $meetings_to_asses = [
            'BOT Meetings(Special & Regular)',
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
                'title' => 'A.  Performance of Role as Corporate Officer - 70 % ',
                'questions' => $questions,
                'add_remarks' => false,
            ],
            [
                'title' => 'B.  Attendance in BOT / Committee Meetings & other related activities (to be rated by the Corporate Secretary) -30%',
                'questions' => $questions,
                'add_remarks' => true,
            ],
        ];

        $data = [
            'title' => 'Evaluation Result C1',
            'date' => date('m/d/Y'),
            'sections' => $sections,
            'rating_scale' => $this->getAttendanceRatingScale(),
            'attendance' => ['criteria' => $attendance_criteria, 'meetings' => $meetings_to_asses],
        ];
        $filename = 'evaluation_result_c1';
        return $this->exportPDF('pdf.evaluation_results.c1', $data, $filename);
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
