<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ReportsController extends Controller
{
    public function bot_performance_summary(Request $request)
    {
        $report_id = $request->report;
        $download = $request->download;
        $report = Report::find($report_id);

        if (!$report) return abort(404);

        if ($report->report_type_id == 1) { // BOT Performance Summary
            $collections = collect([
                [
                    'id' => 1,
                    'code' => 'BOT',
                    'ef_id' => [2, 3],
                    'group_by_committee' => false,
                    'name' => 'Summary of BOT Evaluation',
                    'header' => 'Member Board of Trustees',
                    'header2' => 'Governance Committee<br>Rating(Form C.2 to C.3)<br>(70%)',
                    'weight_distribution' => "<span style='color: red'>*</span> Weight Distribution is 70% Governance Rating and 30% attendance"
                ],
                [
                    'id' => 2,
                    'code' => 'CO',
                    'ef_id' => [4, 5, 6],
                    'group_by_committee' => false,
                    'name' => 'Summary of Corporate Evaluation',
                    'header' => 'Corporate Officers',
                    'header2' => 'Governance Committee<br>Rating(Form C.4 to C.6)<br>(70%)',
                    'weight_distribution' => "<span style='color: red'>*</span> Weight Distribution is 70% Governance Rating and 30% attendance"
                ],
                [
                    'id' => 3,
                    'code' => 'LRP',
                    'ef_id' => [7],
                    'group_by_committee' => true, // toggle here
                    'name' => 'Summary of Lead Resource Persons Evaluation',
                    'header' => 'Lead Resource Person',
                    'header2' => 'Committee Members<br>Rating(Form C.7)(70%)',
                    'weight_distribution' => "<span style='color: red'>*</span> Weight Distribution is 70% Committee Members' Rating and 30% attendance"
                ],
            ]);
        }

        $collections = $collections->map(function ($collection) use ($report) {
            $collection['members'] = $this->get_data($report, $collection['ef_id'], $collection['group_by_committee']);
            return $collection;
        });

        $evaluation_period = Carbon::parse($report->evaluationPeriod->date_from)->format('F d, Y') . ' TO ' . Carbon::parse($report->evaluationPeriod->date_to)->format('F d, Y');
        $data = [
            'evaluation_period' => $evaluation_period,
            'rating_scales'     => $this->get_rating_scale(),
            'report_type'       => $report->reportType->name,
            'collections'       => $collections,
        ];

        $file_name = 'BOT Performance Summary ('.$evaluation_period.').pdf';

        $footer = view('pdf.reports.footer')->render();

        $pdf = PDF::loadView('pdf.reports.bot-performance-summary', compact('data'))
            ->setOption('encoding', 'UTF-8')
            ->setOptions(['margin-bottom' => 10])
            ->setOption('footer-html', $footer)
            ->setOption('enable-local-file-access', true)
            ->setOption('images', true);

        if($download){
            return $pdf->download($file_name);
        }
        return $pdf->inline($file_name);
    }

    public function download_bot_performance_summary(Request $request){

        $pdf = $this->bot_performance_summary($request);

        return $pdf->download('BOT Performance Summary.pdf');
    }
    public function get_data($report, $ef_id, $group_by_committee = false)
    {
        $members = $report->evaluationPeriod->assignments
            ->when($ef_id, fn ($collection) =>
            $collection->whereIn('ef_id', $ef_id)
            )
            ->when(
                $group_by_committee,

                fn ($collection) => $collection
                    ->groupBy('committee_id')
                    ->map(fn ($committeeAssignments) => [
                        'committee_name' => $committeeAssignments->first()->committee?->name, // get committee name
                        'members' => $committeeAssignments
                            ->groupBy('member_id')
                            ->map(fn ($assignments) => $this->map_member_data($assignments))
                            ->values()
                    ]),

                fn ($collection) => $collection
                    ->groupBy('member_id')
                    ->map(fn ($assignments) => $this->map_member_data($assignments))
                    ->values()
            );

        return $members;
    }

    private function map_member_data($assignments): array
    {
        $member = $assignments->first()->member;

        // Average of questionnaire answers
        $assessment_scores       = $assignments->flatMap->assesment_answer;
        $assessment_quantitative = $assessment_scores->avg(fn($a) => $a->ratingScaleValue?->value);
        $assessment_qualitative  = $assessment_quantitative ? $assessment_scores->last()?->ratingScaleValue?->qualitative : null;

        // Average of attendance answers
        $attendance_scores       = $assignments->flatMap->attendance_answer;
        $attendance_quantitative = $attendance_scores->avg(fn($a) => $a->ratingScaleValue?->value);
        $attendance_qualitative  = $attendance_quantitative ? $attendance_scores->last()?->ratingScaleValue?->qualitative : null;

        // Total: 70% assessment + 30% attendance
        $total_quantitative = null;
        if ($assessment_quantitative !== null && $attendance_quantitative !== null) {
            $total_quantitative = ($assessment_quantitative * 0.70) + ($attendance_quantitative * 0.30);
        }

        return [
            'name' => $member?->name,
            'assessment_quantitative' => $assessment_quantitative,
            'assessment_qualitative' => $assessment_qualitative,
            'attendance_quantitative' => $attendance_quantitative,
            'attendance_qualitative' => $attendance_qualitative,
            'total_quantitative' => $total_quantitative,
            'total_qualitative' => $total_quantitative ? $assessment_scores->last()?->ratingScaleValue?->qualitative : null,
        ];
    }

    public function get_rating_scale()
    {
        return [
            [
                'assessment_quantitative' => '4.50 to 5.00',
                'assessment_qualitative' => 'Excellent',
                'attendance_name' => '90% to 100% of the time',
                'attendance_quantitative' => 5,
                'attendance_qualitative' => 'Excellent',
            ],
            [
                'assessment_quantitative' => '3.50 to < 4.50',
                'assessment_qualitative' => 'Superior',
                'attendance_name' => '70 to < 90% of the time',
                'attendance_quantitative' => 4,
                'attendance_qualitative' => 'Superior',
            ],
            [
                'assessment_quantitative' => '2.50 to < 3.50',
                'assessment_qualitative' => 'Very Good',
                'attendance_name' => '50% to < 70% of the time',
                'attendance_quantitative' => 3,
                'attendance_qualitative' => 'Very Good',
            ],
            [
                'assessment_quantitative' => '1.50 to < 2.50',
                'assessment_qualitative' => 'Good',
                'attendance_name' => '30% to < 50% of the time',
                'attendance_quantitative' => 2,
                'attendance_qualitative' => 'Good',
            ],
            [
                'assessment_quantitative' => 'Below 1.50',
                'assessment_qualitative' => 'Satisfactory',
                'attendance_name' => 'Below 30% of the time',
                'attendance_quantitative' => 1,
                'attendance_qualitative' => 'Satisfactory',
            ],
        ];
    }
//    public function summary_of_bot_evaluation(Request $request){
//
//        $report_id = $request->report;
//
//        $report = Report::find($report_id);
//
//        if(!$report) return abort(404);
//
//
//        if($report->report_type_id == 1){ // BOT Performance Summary
//            $collections = collect([
//                [
//                    'id' => 1,
//                    'code' => 'BOT',
//                    'ef_id' => [2,3],
//                    'name' => 'Summary of BOT Evaluation',
//                    'header' => 'Member Board of Trustees',
//                    'header2' => 'Governance Committee<br>Rating(Form C.2 to C.3)<br>(70%)',
//                    'weight_distribution' => "<span style='color: red'>*</span> Weight Distribution is 70% Governance Rating and 30% attendance"
//                ],
//                [
//                    'id' => 2,
//                    'code' => 'CO',
//                    'ef_id' => [4,5,6],
//                    'name' => 'Summary of Corporate Evaluation',
//                    'header' => 'Corporate Officers',
//                    'header2' => 'Governance Committee<br>Rating(Form C.4 to C.6)<br>(70%)',
//                    'weight_distribution' => "<span style='color: red'>*</span> Weight Distribution is 70% Governance Rating and 30% attendance"
//                ],
//                [
//                    'id' => 3,
//                    'code' => 'LRP',
//                    'ef_id' => [7],
//                    'name' => 'Summary of Lead Resource Persons Evaluation',
//                    'header' => 'Lead Resource Person',
//                    'header2' => 'Committee Members<br>Rating(Form C.7)(70%)',
//                    'weight_distribution' => "<span style='color: red'>*</span> Weight Distribution is 70% Committee Members' Rating and 30% attendance"
//                ],
//            ]);
//        }
//
//
//        $collections = $collections->map(function ($collection) use ($report) {
//            $collection['members'] = $this->get_data($report, $collection['ef_id']);
//            return $collection;
//        });
//
//        $data = [
//            'evaluation_period' => Carbon::parse($report->evaluationPeriod->date_from)->format('F d, Y') . ' TO ' . Carbon::parse($report->evaluationPeriod->date_to)->format('F d, Y'),
//            'rating_scales' => $this->get_rating_scale(),
//            'report_type' => $report->reportType->name,
//            'collections' => $collections,
//        ];
//
//        $footer = view('pdf.reports.footer')->render();
//
//        $pdf = PDF::loadView('pdf.reports.summary-of-bot-evaluation',
//            compact('data'))
//            ->setOption('encoding', 'UTF-8')
//            ->setOptions(['margin-bottom' => 10])
//            ->setOption('footer-html',$footer)
//            ->setOption('enable-local-file-access', true)
//            ->setOption('images', true);
//
//        return $pdf->inline('summary-of-bot-evaluation.pdf');
//    }
//

//
//    public function get_data($report,$ef_id){
//
//        $members = $report->evaluationPeriod->assignments
//            ->when($ef_id, fn ($collection) =>
//                $collection->whereIn('ef_id', $ef_id)
//            )
////            ->where('status_id','=',3) // Evaluation Period Status = Completed
////            ->where('trustee_evaluation_statuses_id','=',2) //  Submitted
//                ->groupBy('committee_id')
//            ->groupBy('member_id')
//            ->map(function ($assignments) {
//                $member = $assignments->first()->member;
//
//                // Average of questionnaire answers
//                $assessment_scores = $assignments->flatMap->assesment_answer;
//                $assessment_quantitative = $assessment_scores->avg(fn($a) => $a->ratingScaleValue?->value);
//                $assessment_qualitative  = $assessment_scores->avg(fn($a) => $a->ratingScaleValue?->value) ? $assessment_scores->last()?->ratingScaleValue?->qualitative : null;
//
//                // Average of attendance answers
//                $attendance_scores = $assignments->flatMap->attendance_answer;
//                $attendance_quantitative = $attendance_scores->avg(fn($a) => $a->ratingScaleValue?->value);
//                $attendance_qualitative  = $attendance_scores->avg(fn($a) => $a->ratingScaleValue?->value) ? $attendance_scores->last()?->ratingScaleValue?->qualitative : null;
//
//                // Weighted total: 70% assessment + 30% attendance
//                $total_quantitative = null;
//                if ($assessment_quantitative !== null && $attendance_quantitative !== null) {
//                    $total_quantitative = ($assessment_quantitative * 0.70) + ($attendance_quantitative * 0.30);
//                }
//
//                return [
//                    'name'                    => $member?->name,
//                    'assessment_quantitative' => $assessment_quantitative,
//                    'assessment_qualitative'  => $assessment_qualitative,
//                    'attendance_quantitative' => $attendance_quantitative,
//                    'attendance_qualitative'  => $attendance_qualitative,
//                    'total_quantitative'      => $total_quantitative,
//                    'total_qualitative'       => $total_quantitative ? $assessment_scores->last()?->ratingScaleValue?->qualitative : null,
//                ];
//            })
//            ->values();
//
//        dd($members);
//        return $members;
//    }
//
//    public function get_rating_scale(){
//
//        $rating_scales = [
//            [
//                'assessment_quantitative' => '4.50 to 5.00',
//                'assessment_qualitative'  => 'Excellent',
//                'attendance_name'         => '90% to 100% of the time',
//                'attendance_quantitative' => 5,
//                'attendance_qualitative'  => 'Excellent',
//            ],
//            [
//                'assessment_quantitative' => '3.50 to < 4.50',
//                'assessment_qualitative'  => 'Superior',
//                'attendance_name'         => '70 to < 90% of the time',
//                'attendance_quantitative' => 4,
//                'attendance_qualitative'  => 'Superior',
//            ],
//            [
//                'assessment_quantitative' => '2.50 to < 3.50',
//                'assessment_qualitative'  => 'Very Good',
//                'attendance_name'         => '50% to < 70% of the time',
//                'attendance_quantitative' => 3,
//                'attendance_qualitative'  => 'Very Good',
//            ],
//            [
//                'assessment_quantitative' => '1.50 to < 2.50',
//                'assessment_qualitative'  => 'Good',
//                'attendance_name'         => '30% to < 50% of the time',
//                'attendance_quantitative' => 2,
//                'attendance_qualitative'  => 'Good',
//            ],
//            [
//                'assessment_quantitative' => 'Below 1.50',
//                'assessment_qualitative'  => 'Satisfactory',
//                'attendance_name'         => 'Below 30% of the time',
//                'attendance_quantitative' => 1,
//                'attendance_qualitative'  => 'Satisfactory',
//            ],
//        ];
//
//        return $rating_scales;
//    }

}
