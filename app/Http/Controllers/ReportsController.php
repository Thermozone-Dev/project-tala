<?php

namespace App\Http\Controllers;

use App\Actions\AssesmentComputation;
use App\Models\AttendanceAnswer;
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
        }else{
            return;
        }


        $collections = $collections->map(function ($collection) use ($report) {
            $collection['members'] = collect(
                $this->get_data($report, $collection['ef_id'], $collection['group_by_committee'])
                )->map(function ($item) use ($report) {

                    if (isset($item['member_id'])) {
                        return $this->enrichMember($item, $report);
                    }


                    if (isset($item['members'])) {
                        $item['members'] = collect($item['members'])
                            ->map(fn ($member) => $this->enrichMember($member, $report))
                            ->values();

                        return $item;
                    }

                    return $item;

                })->values();
            return $collection;
        });
        // dd($collections);

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

    private function enrichMember($member, $report)
    {
        $attendance = AssesmentComputation::calculate_attendance_rating_per_member(
            $member['member_id'],
            $report->evaluationPeriod->id
        );

        $performance_summary = AssesmentComputation::calculate_performance_summary(
            $attendance['average_grade'],
            $member['assessment_quantitative']
        );

        $member['attendance_quantitative'] = $attendance['average_grade'];
        $member['attendance_qualitative'] = $attendance['rating'];
        $member['total_quantitative'] = $performance_summary['quantitative'];
        $member['total_qualitative'] = $performance_summary['qualitative'];

        return $member;
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
        $assessment_qualitative = AssesmentComputation::get_assesment_rating_bot_summary($assessment_quantitative);

        return [
            'member_id' => $member->id,
            'name' => $member?->name,
            'assessment_quantitative' => $assessment_quantitative,
            'assessment_qualitative' => $assessment_qualitative,
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
}
