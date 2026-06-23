<?php

namespace App\Http\Controllers;

use App\Actions\AssesmentComputation;
use App\Models\AttendanceAnswer;
use App\Models\Report;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ReportsController extends Controller
{
    public ?Report $report = null;

    public function preview_report(Request $request)
    {
        // dd($request);
        $report_id = $request->report;
        $download = $request->download;
        $this->report = Report::find($report_id);
        // $this->report = Report::find(2);


        if (!$this->report) return abort(404);

        $report = $this->report; // Assign to local variable for use in all methods

        switch ($report->report_type_id) {
            case 1: // BOT Performance Summary

                $pay_load = [
                    'blade_path' => 'pdf.reports.bot-performance-summary',
                    'page_orientation' => 'portrait',
                    'collections' => $this->bot_performance_summary_collection($report),
                ];

                break;
            case 2: // Individual Results of Rating - BOT

                $pay_load = [
                    'blade_path' => 'pdf.reports.individual-results-of-rating-bot',
                    'page_orientation' => 'landscape',
                    'collections' => $this->indiviual_results_of_rating_bot_collection(),
                ];

                break;
            case 3: // Individual Results of Rating - CO & LRPs

                $pay_load = [
                    'blade_path' => 'pdf.reports.individual-results-of-rating-CO-and-LRPs',
                    'page_orientation' => 'landscape',
                    'collections' => $this->indiviual_results_of_rating_co_and_lrps_collection($report),
                ];

                break;
            case 4: // Summary Results of Committee Assessment

                $pay_load = [
                    'blade_path' => 'pdf.reports.summary-results-of-committee-assessment',
                    'page_orientation' => 'landscape',
                    'collections' => $this->summary_results_of_committee_assessment_collection($report),
                ];

                break;
            default:
                return;
        }

        $evaluation_period = Carbon::parse($report->evaluationPeriod->date_from)->format('F d, Y') . ' TO ' . Carbon::parse($report->evaluationPeriod->date_to)->format('F d, Y');
        $data = [
            'evaluation_period' => $evaluation_period,
            'rating_scales'     => $this->get_rating_scale(),
            'report_type'       => $report->reportType->name,
            'collections'       => $pay_load['collections'],
        ];
        $file_name = $report->reportType->name.' ('.$evaluation_period.').pdf';

        $footer = view('pdf.reports.footer')->render();

        $pdf = PDF::loadView($pay_load['blade_path'], compact('data'))
            ->setOption('encoding', 'UTF-8')
            ->setOptions(['margin-bottom' => 10])
            ->setOrientation($pay_load['page_orientation'])
            ->setOption('footer-html', $footer)
            ->setOption('enable-local-file-access', true)
            ->setOption('images', true);

        if($download){
            return $pdf->download($file_name);
        }
        return $pdf->inline($file_name);
    }

    public function bot_performance_summary_collection($report){

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

        return $collections;
    }

    /**
     * Get individual results of rating for BOT (Trustee/Chairman)
     * Organized by: Member -> Question -> Evaluators
     */
    public function indiviual_results_of_rating_bot_collection(){
        $members = $this->get_members_evaluation_summary_by_roles(['Trustee', 'Chairman', 'Vice Chairman']);
        $members = $this->individual_summary_mapper($members);
        return $members;
    }

    public function individual_summary_mapper ($members){
        return $members->map(function ($member) {
            // Group by question and map with evaluator info
            $questionsByEvaluator = collect($member['evaluation_summary'])
                ->flatMap(function ($evaluatorGroup) {
                    $evaluatorName = $evaluatorGroup['evaluator_name'];
                    $evaluatorID = $evaluatorGroup['evaluator_id'];

                    return collect($evaluatorGroup['evaluations'])
                        ->flatMap(fn($evaluation) => $evaluation['answers'])
                        ->map(fn($answer) => array_merge($answer, ['evaluator_name' => $evaluatorName,'evaluator_id' => $evaluatorID]));
                });
            // Group by question with totals and averages
            $questionGroups = $questionsByEvaluator->groupBy('question_id')->map(function ($answers) {
                $firstAnswer = $answers->first();
                $answerValues = $answers->pluck('answer_value')->filter(fn($v) => $v !== null);
                $avgRating = $answerValues->count() > 0 ? round($answerValues->avg(), 2) : 0;
                $qualitativeRating = AssesmentComputation::get_assesment_rating_bot_summary($avgRating);

                return [
                    'question_id' => $firstAnswer['question_id'],
                    'question' => $firstAnswer['question'],
                    'total_rating' => $answerValues->sum(),
                    'average_rating' => $avgRating,
                    'qualitative_rating' => $qualitativeRating,
                    'evaluators' => $answers->map(fn($answer) => [
                        'evaluator_id' => $answer['evaluator_id'],
                        'evaluator_name' => $answer['evaluator_name'],
                        'answer_value' => $answer['answer_value'],
                        'answer_qualitative' => $answer['answer_qualitative'],
                        'remarks' => $answer['remarks']
                    ])->values()->toArray()
                ];
            })->values();

            // Calculate evaluator summaries
            $evaluatorSummaries = $questionsByEvaluator->groupBy('evaluator_name')->map(function ($answers) {
                $answerValues = $answers->pluck('answer_value')->filter(fn($v) => $v !== null);
                return [
                    'evaluator_id' => $answers->first()['evaluator_id'],
                    'evaluator_name' => $answers->first()['evaluator_name'],
                    'total_rating' => $answerValues->sum(),
                    'average_rating' => $answerValues->count() > 0 ? round($answerValues->avg(), 2) : 0,
                    'total_evaluations' => $answerValues->count()
                ];
            })->values();

            $total_rating = $questionGroups->sum('total_rating');
            $total_average = $questionGroups->avg('total_rating');
            $rating_average = $questionGroups->avg('average_rating');

            $total_qualitative = AssesmentComputation::get_assesment_rating_bot_summary($rating_average);

            return [
                'member_id' => $member['member_id'],
                'member_name' => $member['member_name'],
                'member_email' => $member['member_email'],
                'total_rating' => $total_rating,
                'total_average' => $total_average,
                'rating_average' => $rating_average,
                'total_qualitative' => $total_qualitative,
                'roles' => $member['roles'],
                'questions' => $questionGroups->toArray(),
                'evaluators_summary' => $evaluatorSummaries->toArray()
            ];
        })->values();
    }

    /**
     * Get individual results of rating for CO & LRPs (Corporate Officers & Lead Resource Persons)
     * Uses get_members_evaluation_summary_by_roles for flexibility
     */
    public function indiviual_results_of_rating_co_and_lrps_collection($report){
        return $this->get_members_evaluation_summary_by_roles([
            'Corporate Officer',
            'Corporate Treasurer',
            'Corporate Comptroller',
            'Corporate Secretary',
            'Lead Resource Person'
        ]);
    }
    public function summary_results_of_committee_assessment_collection($report){
        return null;
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
                'scale' => ''
            ],
            [
                'assessment_quantitative' => '3.50 to < 4.50',
                'assessment_qualitative' => 'Superior',
                'attendance_name' => '70 to < 90% of the time',
                'attendance_quantitative' => 4,
                'attendance_qualitative' => 'Superior',
                'scale' => 'Strongly Agree'
            ],
            [
                'assessment_quantitative' => '2.50 to < 3.50',
                'assessment_qualitative' => 'Very Good',
                'attendance_name' => '50% to < 70% of the time',
                'attendance_quantitative' => 3,
                'attendance_qualitative' => 'Very Good',
                'scale' => 'Somewhat Agree'
            ],
            [
                'assessment_quantitative' => '1.50 to < 2.50',
                'assessment_qualitative' => 'Good',
                'attendance_name' => '30% to < 50% of the time',
                'attendance_quantitative' => 2,
                'attendance_qualitative' => 'Good',
                'scale' => 'Somewhat Disagree'
            ],
            [
                'assessment_quantitative' => 'Below 1.50',
                'assessment_qualitative' => 'Satisfactory',
                'attendance_name' => 'Below 30% of the time',
                'attendance_quantitative' => 1,
                'attendance_qualitative' => 'Satisfactory',
                'scale' => 'Strongly Disagree'
            ],
        ];
    }

    /**
     * Get all evaluations per member
     * Captures: evaluator name, member evaluated, evaluation period, and question answers
     * Handles null values by converting them to 0
     */
    public function member_evaluation_method($evaluator_id = null)
    {
        $query = TrusteeHasEvaluation::with([
            'evaluator',
            'member',
            'evaluationPeriod',
            'form',
            'assesment_answer.questionnaire',
            'assesment_answer.ratingScaleValue'
        ]);

        // Filter by evaluation period if $report is set
        if ($this->report) {
            $query->where('evaluation_id', $this->report->evaluationPeriod->id);
        }

        // Filter by evaluator if provided
        if ($evaluator_id) {
            $query->where('evaluator_id', $evaluator_id);
        }

        return $query->get()->map(function ($evaluation) {
            // Handle case where no answers have been encoded yet
            $answers = $evaluation->assesment_answer->isEmpty()
                ? collect() // Empty collection if no answers
                : $evaluation->assesment_answer->map(function ($answer) {
                    return [
                        'question_id' => $answer->questionnaire_id ?? null,
                        'question' => $answer->questionnaire->name ?? 'N/A',
                        'answer_value' => $answer->ratingScaleValue?->value ?? 0, // Default to 0 if null
                        'answer_qualitative' => $answer->ratingScaleValue?->qualitative ?? 'No Rating',
                        'remarks' => $answer->remarks ?? '' // Empty string if no remarks
                    ];
                });

            return [
                'evaluation_id' => $evaluation->id,
                'evaluator_id' => $evaluation?->evaluator?->id ?? null,
                'member_evaluated_id' => $evaluation->member?->id ?? null,
                'evaluator_name' => $evaluation->evaluator->full_name ?? 'N/A',
                'member_evaluated' => $evaluation->member?->full_name ?? 'N/A',
                'evaluation_period' => $evaluation->evaluationPeriod->formatted_coverage ?? 'N/A',
                'evaluation_form' => $evaluation->form->title ?? 'N/A',
                'status' => $evaluation->eval_status->name ?? 'Unknown',
                'answers' => $answers->toArray(),
                'has_answers' => $evaluation->assesment_answer->isNotEmpty()
            ];
        });
    }

    /**
     * Get members evaluation summaries by roles
     * Flexible method that accepts role(s) and loops through members to get their individual evaluation data
     * Can be reused for trustee/chairman, LRPs, corporate officers, etc.
     *
     * @param array|string $roles Single role or array of roles (e.g., 'Trustee', ['Trustee', 'Chairman'])
     * @return \Illuminate\Support\Collection
     */
    public function get_members_evaluation_summary_by_roles($roles)
    {
        // Convert single role string to array
        if (is_string($roles)) {
            $roles = [$roles];
        }

        // Get all users with specified roles
        $members = \App\Models\User::role($roles)
            ->with('roles')
            ->get();

        return $members->map(function ($member) {
            return [
                'member_id' => $member->id,
                'member_name' => $member->full_name,
                'member_email' => $member->email,
                'roles' => $member->roles->pluck('name')->toArray(),
                'evaluation_summary' => $this->get_evaluation_result_as_member($member->id)
            ];
        })->values();
    }

    /**
     * Get all trustee/chairman members and their evaluation summaries
     * Wrapper method for convenience - uses get_members_evaluation_summary_by_roles()
     */
    public function get_all_trustee_chairman_evaluation_summary()
    {
        return $this->get_members_evaluation_summary_by_roles(['Trustee', 'Chairman', 'Vice Chairman']);
    }

    /**
     * Get evaluation results for a specific member (as the evaluated member)
     * Uses member_evaluation_method() data and filters by member_id
     * Handles null values and unanswered questions by defaulting to 0
     * Shows all evaluations ABOUT this member organized by evaluator
     */
    public function get_evaluation_result_as_member($member_id)
    {
        $allEvaluations = $this->member_evaluation_method();

        // Filter to get only evaluations done ON/ABOUT this specific member
        $memberEvaluations = $allEvaluations->filter(function ($evaluation) use ($member_id) {
            return $evaluation['member_evaluated_id'] == $member_id;
        })->values();

        return $memberEvaluations->groupBy('evaluator_id')->map(function ($evaluatorGroup, $evaluatorId) use ($member_id) {
            $member = User::where('id', $member_id)->first();
            $evaluatorName = $evaluatorGroup->first()['evaluator_name'] ?? 'N/A';

            return [
                'member_id' => $member?->id ?? null,
                'member_evaluated_id' => $member_id,
                'evaluator_id' => $evaluatorId,
                'evaluator_name' => $evaluatorName,
                'total_evaluations' => $evaluatorGroup->count(),
                'evaluations' => $evaluatorGroup->map(function ($eval) use ($member, $member_id, $evaluatorId) {
                    // Map answers with default 0 for null/missing values
                    $processedAnswers = collect($eval['answers'])->map(function ($answer) {
                        return [
                            'question_id' => $answer['question_id'] ?? 'N/A',
                            'question' => $answer['question'] ?? 'N/A',
                            'answer_value' => $answer['answer_value'] ?? 0, // Default to 0
                            'answer_qualitative' => $answer['answer_qualitative'] ?? 'No Rating',
                            'remarks' => $answer['remarks'] ?? ''
                        ];
                    })->toArray();

                    return [
                        'member_id' => $member?->id ?? null,
                        'member_evaluated_id' => $member_id,
                        'evaluator_id' => $evaluatorId,
                        'member_name' => $member?->full_name ?? null,
                        'evaluation_form' => $eval['evaluation_form'] ?? 'N/A',
                        'evaluation_period' => $eval['evaluation_period'] ?? 'N/A',
                        'status' => $eval['status'] ?? 'Unknown',
                        'has_answers' => $eval['has_answers'] ?? false,
                        'total_answers' => count($processedAnswers),
                        'answers' => $processedAnswers
                    ];
                })->toArray()
            ];
        })->values();
    }
}
