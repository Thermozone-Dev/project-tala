<?php

namespace App\Http\Controllers;

use App\Actions\AssesmentComputation;
use App\Models\AttendanceAnswer;
use App\Models\Committee;
use App\Models\Report;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsController extends Controller
{
    public ?Report $report = null;

    public function preview_report(Request $request)
    {
        // dd($request);
        $report_id = $request->report;
        $download = $request->download;
        $excel = $request->boolean('excel');
        $this->report = Report::find($report_id);
        // $this->report = Report::find(2);


        if (!$this->report) return abort(404);

        $report = $this->report; // Assign to local variable for use in all methods
        $evaluation_period_formatted = Carbon::parse($report->evaluationPeriod->date_from)->format('F d, Y') . ' TO ' . Carbon::parse($report->evaluationPeriod->date_to)->format('F d, Y');

        switch ($report->report_type_id) {
            case 1: // BOT Performance Summary

                $pay_load = [
                    'blade_path' => 'pdf.reports.bot-performance-summary',
                    'excel_blade_path' => 'excel.bot-performance-summary',
                    'page_orientation' => 'portrait',
                    'collections' => $this->bot_performance_summary_collection($report),
                ];

                break;
            case 2: // Individual Results of Rating - BOT

                $pay_load = [
                    'blade_path' => 'pdf.reports.individual-results-of-rating-bot',
                    'excel_blade_path' => 'excel.individual-results-of-rating-bot',
                    'page_orientation' => 'landscape',
                    'collections' => $this->indiviual_results_of_rating_bot_collection(),
                ];

                break;
            case 3: // Individual Results of Rating - CO & LRPs

                $coResults = $this->indiviual_results_of_rating_corporate_officers_collection($report);
                $lrpResults = $this->indiviual_results_of_rating_lrps_collection($report);

                $pay_load = [
                    'blade_path' => 'pdf.reports.individual-results-of-rating-CO-and-LRPs',
                    'excel_blade_path' => 'excel.individual-results-of-rating-CO-and-LRPs',
                    'page_orientation' => 'landscape',
                    'collections' => array_merge($coResults, $lrpResults),
                ];

                break;
            case 4: // Summary Results of Committee Assessment

                $pay_load = [
                    'blade_path' => 'pdf.reports.summary-results-of-committee-assessment',
                    'excel_blade_path' => 'excel.summary-results-of-committee-assessment',
                    'page_orientation' => 'landscape',
                    'evaluation_period' => $evaluation_period_formatted,
                    'evaluation_period_obj' => $report->evaluationPeriod,
                    'collections' => $this->summary_of_committee_individual_self_assesment(),
                ];

                break;
            default:
                abort(404);
        }

        $data = [
            'evaluation_period' => $evaluation_period_formatted,
            'evaluation_period_obj' => $report->evaluationPeriod,
            'rating_scales'     => $this->get_rating_scale(),
            'report_type'       => $report->reportType->name,
            'collections'       => $pay_load['collections'],
        ];

        if ($excel) {

            if (!isset($pay_load['excel_blade_path'])) {
                abort(404, 'Excel export is not available for this report.');
            }

            if($pay_load['excel_blade_path'] == 'excel.summary-results-of-committee-assessment'){
                return $this->downloadCommitteeAssessmentExcel(
                    $pay_load,
                    $pay_load['excel_blade_path'],
                    $report->reportType->name
                );
            }

            return $this->downloadExcelReport(
                $data,
                $pay_load['excel_blade_path'],
                $report->reportType->name,
                $pay_load['excel_column_widths'] ?? []
            );
        }

        $file_name = $report->reportType->name.' ('.$evaluation_period_formatted.').pdf';

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
        // Get BOT individual results
        $botMembers = $this->indiviual_results_of_rating_bot_collection();
        $botSummary = [
            'id' => 1,
            'code' => 'BOT',
            'name' => 'Board of Trustees',
            'members' => $botMembers->map(function ($member) {
                return [
                    'member_id' => $member['member_id'],
                    'member_name' => $member['member_name'],
                    'rating_average' => $member['rating_average'],
                    'total_qualitative' => $member['total_qualitative'],
                    'attendance_rating' => $member['attendance_rating'],
                    'attendance_avg_rating' => $member['attendance']['summary']['avg_rating'] ?? 0,
                    'attendance_qualitative' => $member['attendance']['summary']['attendance_rating_qualititative'] ?? 'No Rating',
                    'final_grade_quantitative' => $member['final_grade']['quantitative'] ?? 0,
                    'final_grade_qualitative' => $member['final_grade']['qualitative'] ?? 'No Rating'
                ];
            })->values()->toArray()
        ];

        // Get CO and LRP individual results using new separate methods
        $coMembers = $this->indiviual_results_of_rating_corporate_officers_collection($report);
        // Get LRP members WITHOUT deduplication (keep all committee entries)
        $lrpEvaluations = $this->get_lrp_evaluations_for_summary($report);

        // Format CO results for summary display
        $corporateOfficersDedup = collect($coMembers)
            ->unique('member_id')
            ->map(function ($member) {
                // Get all corporate officer roles for this user
                $user = User::find($member['member_id']);
                $allCOofficerRoles = $user->roles()
                    ->whereIn('name', ['Corporate Secretary', 'Treasurer', 'Comptroller', 'EVP-GM'])
                    ->pluck('name')
                    ->toArray();

                $rolesDisplay = !empty($allCOofficerRoles) ? implode(', ', $allCOofficerRoles) : 'Corporate Officer';

                return [
                    'member_id' => $member['member_id'],
                    'member_name' => $member['member_name'],
                    'roles' => $rolesDisplay,
                    'rating_average' => $member['rating_average'],
                    'total_qualitative' => $member['total_qualitative'],
                    'attendance_rating' => $member['attendance_rating'],
                    'attendance_avg_rating' => $member['attendance']['summary']['avg_rating'] ?? 0,
                    'attendance_qualitative' => $member['attendance']['summary']['attendance_rating_qualititative'] ?? 'No Rating',
                    'final_grade_quantitative' => $member['final_grade']['quantitative'] ?? 0,
                    'final_grade_qualitative' => $member['final_grade']['qualitative'] ?? 'No Rating'
                ];
            })
            ->values();

        // Format LRP results for summary display (grouped by committee) - preserve all committee entries
        $lrps = collect($lrpEvaluations)->map(function ($member) {
            return [
                'member_id' => $member['member_id'],
                'member_name' => $member['member_name'],
                'committee_id' => $member['committee_id'] ?? null,
                'committee_name' => $member['committee_name'] ?? 'Unknown',
                'rating_average' => $member['rating_average'],
                'total_qualitative' => $member['total_qualitative'],
                'attendance_rating' => $member['attendance_rating'],
                'attendance_avg_rating' => $member['attendance']['summary']['avg_rating'] ?? 0,
                'attendance_qualitative' => $member['attendance']['summary']['attendance_rating_qualititative'] ?? 'No Rating',
                'final_grade_quantitative' => $member['final_grade']['quantitative'] ?? 0,
                'final_grade_qualitative' => $member['final_grade']['qualitative'] ?? 'No Rating'
            ];
        });

        $coSummary = [
            'id' => 2,
            'code' => 'CO',
            'name' => 'Corporate Officers',
            'members' => $corporateOfficersDedup->toArray()
        ];

        $lrpSummary = [
            'id' => 3,
            'code' => 'LRP',
            'name' => 'Lead Resource Persons',
            'members' => $lrps->groupBy('committee_id')->map(function ($lrpGroup) {
                return [
                    'committee_id' => $lrpGroup->first()['committee_id'],
                    'committee_name' => $lrpGroup->first()['committee_name'],
                    'members' => $lrpGroup->values()->toArray()
                ];
            })->values()->toArray()
        ];

        $collections = collect([$botSummary, $coSummary, $lrpSummary]);
        // dd($collections);
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

    /**
     * Build evaluation summary from filtered TrusteeHasEvaluation records
     * Ensures only the specified form IDs are included
     */
    private function build_evaluation_summary_from_assignments($assignments)
    {
        return $assignments->groupBy('evaluator_id')->map(function ($evaluatorGroup) {
            $firstAssignment = $evaluatorGroup->first();
            $evaluator = $firstAssignment->evaluator;

            // Skip if evaluator doesn't exist
            if (!$evaluator) {
                return null;
            }

            return [
                'evaluator_id' => $evaluator->id,
                'evaluator_name' => $evaluator->full_name ?? 'N/A',
                'total_evaluations' => $evaluatorGroup->count(),
                'evaluations' => $evaluatorGroup->map(function ($assignment) {
                    $answers = $assignment->assesment_answer->isEmpty()
                        ? collect()
                        : $assignment->assesment_answer->map(function ($answer) {
                            return [
                                'question_id' => $answer->questionnaire_id ?? null,
                                'question' => $answer->questionnaire->name ?? 'N/A',
                                'answer_value' => $answer->ratingScaleValue?->value ?? 0,
                                'answer_qualitative' => $answer->ratingScaleValue?->qualitative ?? 'No Rating',
                                'remarks' => $answer->remarks ?? ''
                            ];
                        });

                    return [
                        'member_id' => $assignment->member_id,
                        'evaluator_id' => $assignment->evaluator_id,
                        'evaluation_form' => $assignment->form?->title ?? 'N/A',
                        'evaluation_period' => $assignment->evaluationPeriod?->formatted_coverage ?? 'N/A',
                        'status' => $assignment->eval_status?->name ?? 'Unknown',
                        'has_answers' => $assignment->assesment_answer->isNotEmpty(),
                        'total_answers' => $answers->count(),
                        'answers' => $answers->toArray()
                    ];
                })->toArray()
            ];
        })->filter()->values()->toArray();
    }

    public function individual_summary_mapper ($members){
        // Deduplicate by member_id to ensure each member appears only once
        $uniqueMembers = collect($members)->unique('member_id')->values();

        return $uniqueMembers->map(function ($member) {
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

            // Use pre-calculated attendance if provided, otherwise calculate it
            if (isset($member['attendance'])) {
                // Use pre-calculated full attendance structure (for CO/LRP filtered views)
                $attendance = $member['attendance'];
                $attendance_rating = $attendance['summary']['avg_rating'] ?? 0;
            } else {
                // Get attendance for member's specific context (BOT or committee)
                $committee_id = isset($member['committee_id']) ? $member['committee_id'] : null;
                $attendance = $this->get_trustee_attendance_evaluation($member['member_id'], $committee_id);
                $attendance_rating = $attendance['summary']['avg_rating'] ?? 0;
            }

            $final_grade = AssesmentComputation::calculate_performance_summary($attendance_rating,$rating_average);

            $result = [
                'member_id' => $member['member_id'],
                'member_name' => $member['member_name'],
                'member_lastname' => $member['member_lastname'],
                'member_email' => $member['member_email'],
                'total_rating' => $total_rating,
                'total_average' => $total_average,
                'rating_average' => $rating_average,
                'total_qualitative' => $total_qualitative,
                'roles' => $member['roles'],
                'questions' => $questionGroups->toArray(),
                'attendance' => $attendance,
                'final_grade' => $final_grade,
                'attendance_rating' => $attendance_rating,
                'evaluators_summary' => $evaluatorSummaries->toArray()
            ];

            // Add committee context if provided (for CO/LRP reports)
            if (isset($member['committee_id'])) {
                $result['committee_id'] = $member['committee_id'];
            }
            $result['committee_name'] = $member['committee_name']?? 'Board';
            // dd($result);
            return $result;
        })->values();
    }

    /**
     * Get attendance evaluation results for a specific member
     * Returns organized attendance data grouped by category (BOT or Committee)
     *
     * @param int $member_id
     * @param int|null $committee_id Filter by specific committee (null for BOT, integer for committee)
     * @return \Illuminate\Support\Collection
     */
    public function get_trustee_attendance_evaluation($member_id, $committee_id = null)
    {
        $query = AttendanceAnswer::where('trustee_id', $member_id)
            ->with(['commitee', 'ratingScaleValue']);

        // Filter by evaluation period if $report is set
        if ($this->report) {
            $query->where('evaluation_period_id', $this->report->evaluationPeriod->id);
        }

        // Filter by specific committee if provided
        if ($committee_id !== null) {
            $query->where('committee_id', $committee_id);
        }

        $attendance = $query->get()->groupBy('committee_id')->map(function ($attendanceGroup, $committee_id) {
            $firstItem = $attendanceGroup->first();
            // Determine category name
            if ($committee_id == null) {
                $category = 'BOT Meetings';
            } else {
                $committee_name = $firstItem->commitee?->name ?? 'Unknown';
                $category = $committee_name . ' Meetings';
            }

            // Get the first attendance record (should be only one per committee per member per period)
            $attendance = $firstItem;

            $total_meetings = $attendance->total_meetings ?? 0;
            $physically_present = $attendance->physically_present ?? 0;
            $considered_present = $attendance->considered_present ?? 0;
            $total_present = $attendance->total_present ?? 0;

            $attendance_percentage = AssesmentComputation::get_attendance_percentage($total_meetings,$total_present);


            return [
                'committee_id' =>$attendance->committee_id,
                'category' => $category,
                'total_meetings' => $total_meetings,
                'physically_present' => $physically_present,
                'considered_present' => $considered_present,
                'total_present' => $total_present,
                'attendance_percentage' => $attendance_percentage,
                'rating_scale' => $attendance->ratingScaleValue?->value  ?? 0,
                'rating_scale_equivalent' => $attendance->ratingScaleValue?->qualitative ?? 'No Rating'
            ];
        })->values();

        $avgRatingScale = $attendance->avg('rating_scale') ?? 0;
        $attendance_rating_qualitative = AssesmentComputation::get_attendance_qualitative($avgRatingScale);
        $attendance_summary = [
            'total_meetings' => $attendance->sum('total_meetings'),
            'physically_present' => $attendance->sum('physically_present'),
            'considered_present' => $attendance->sum('considered_present'),
            'total_present' => $attendance->sum('total_present'),
            'avg_attendance_percentage' => $attendance->avg('attendance_percentage'),
            'avg_rating' => $avgRatingScale,
            'attendance_rating_qualititative' => $attendance_rating_qualitative ?? 'No Rating Found',
        ];

        return [
            'attendance' => $attendance,
            'summary' => collect($attendance_summary)
        ];
    }

    /**
     * Get individual results of rating for Corporate Officers (C3-C6)
     * Gets all evaluations by FORM ID to capture all CO evaluations for each member
     * Organized by: Member -> Question -> Evaluators (no committee context)
     * Attendance: Only from committees where they hold a corporate officer role
     * Forms: C3 (EVP-GM), C4 (Corporate Secretary), C5 (Treasurer), C6 (Comptroller)
     */
    public function indiviual_results_of_rating_corporate_officers_collection($report){
        // Corporate officer form IDs: C3=3, C4=4, C5=5, C6=6
        $coFormIds = [3, 4, 5, 6];
        $coRoles = [
            'Treasurer',
            'Comptroller',
            'Corporate Secretary',
            'EVP-GM'
        ];

        // Verify evaluation period exists
        if (!$report || !$report->evaluationPeriod) {
            return [];
        }

        // Get evaluations by FORM ID (not by member role) to capture all CO evaluations
        $evaluations = $report->evaluationPeriod->assignments
            ->whereIn('ef_id', $coFormIds)
            ->load(['member', 'evaluator', 'form', 'evaluationPeriod', 'eval_status', 'assesment_answer.questionnaire', 'assesment_answer.ratingScaleValue'])
            ->groupBy('member_id')
            ->map(function ($memberAssignments) use ($report, $coRoles) {
                $member = $memberAssignments->first()->member;

                // Skip if member doesn't exist
                if (!$member) {
                    return null;
                }

                // Deduplicate assignments - only keep unique assignment IDs (in case of duplicates)
                $uniqueAssignments = $memberAssignments->unique('id')->values();

                // Build evaluation_summary directly from filtered assignments (only CO forms)
                $evaluationSummary = $this->build_evaluation_summary_from_assignments($uniqueAssignments);

                // Get committees from the CO assignments for this member
                $coCommitteesFromAssignments = $uniqueAssignments->pluck('committee_id')->filter()->unique()->toArray();
                $hasBotAssignments = $uniqueAssignments->contains(fn($a) => $a->committee_id === null);

                // Also get committees where user holds a corporate officer role
                $coCommitteesFromRoles = User::find($member->id)->committees()
                    ->whereHas('role', fn($q) => $q->whereIn('name', $coRoles))
                    ->pluck('committee_id')
                    ->toArray();

                // Combine both sources - use committees from assignments primarily
                $coCommittees = !empty($coCommitteesFromAssignments) ? $coCommitteesFromAssignments : $coCommitteesFromRoles;

                // Build full attendance structure for CO (from CO committees + BOT if applicable)
                $attendanceData = null;
                $attendanceRecords = collect();

                // Query attendance from CO committees
                if (!empty($coCommittees)) {
                    $attendanceRecords = $attendanceRecords->merge(
                        AttendanceAnswer::where('evaluation_period_id', $report->evaluationPeriod->id)
                            ->where('trustee_id', $member->id)
                            ->whereIn('committee_id', $coCommittees)
                            ->with('ratingScaleValue', 'commitee')
                            ->get()
                    );
                }

                // Also query BOT meetings attendance if CO has BOT assignments
                if ($hasBotAssignments) {
                    $attendanceRecords = $attendanceRecords->merge(
                        AttendanceAnswer::where('evaluation_period_id', $report->evaluationPeriod->id)
                            ->where('trustee_id', $member->id)
                            ->whereNull('committee_id')
                            ->with('ratingScaleValue', 'commitee')
                            ->get()
                    );
                }

                // Deduplicate attendance records
                $attendanceRecords = $attendanceRecords->unique('id')->values();

                if ($attendanceRecords->isNotEmpty()) {
                        // Build attendance array with proper structure
                        $attendanceArray = $attendanceRecords->map(function ($attendance) {
                            $attendance_percentage = AssesmentComputation::get_attendance_percentage(
                                $attendance->total_meetings ?? 0,
                                $attendance->total_present ?? 0
                            );

                            $category = $attendance->committee_id === null
                                ? 'BOT Meetings'
                                : ($attendance->commitee?->name . ' Meetings' ?? 'Unknown Committee');

                            return [
                                'committee_id' => $attendance->committee_id,
                                'category' => $category,
                                'total_meetings' => $attendance->total_meetings ?? 0,
                                'physically_present' => $attendance->physically_present ?? 0,
                                'considered_present' => $attendance->considered_present ?? 0,
                                'total_present' => $attendance->total_present ?? 0,
                                'attendance_percentage' => $attendance_percentage,
                                'rating_scale' => $attendance->ratingScaleValue?->value ?? 0,
                                'rating_scale_equivalent' => $attendance->ratingScaleValue?->qualitative ?? 'No Rating'
                            ];
                        })->toArray();

                        // Build summary
                        $avgRatingScale = $attendanceRecords->avg('ratingScaleValue.value') ?? 0;
                        $attendanceSummary = [
                            'total_meetings' => $attendanceRecords->sum('total_meetings'),
                            'physically_present' => $attendanceRecords->sum('physically_present'),
                            'considered_present' => $attendanceRecords->sum('considered_present'),
                            'total_present' => $attendanceRecords->sum('total_present'),
                            'avg_attendance_percentage' => $attendanceRecords->avg(
                                fn($record) => AssesmentComputation::get_attendance_percentage($record->total_meetings, $record->total_present)
                            ),
                            'avg_rating' => $avgRatingScale,
                            'attendance_rating_qualititative' => AssesmentComputation::get_attendance_qualitative($avgRatingScale) ?? 'No Rating'
                        ];

                        $attendanceData = [
                            'attendance' => $attendanceArray,
                            'summary' => $attendanceSummary
                        ];
                    }

                return [
                    'member_id' => $member->id,
                    'member_name' => $member->full_name,
                    'member_lastname' => $member->last_name,
                    'member_email' => $member->email,
                    'roles' => $member->roles->pluck('name')->toArray(),
                    'evaluation_summary' => $evaluationSummary,
                    'attendance' => $attendanceData ?? ['attendance' => [], 'summary' => ['avg_rating' => 0, 'attendance_rating_qualititative' => 'No Rating']],
                ];
            })
            ->filter() // Remove null values
            ->values();

        // Map through individual summary mapper - deduplicate by member_id to prevent duplicates
        $results = $this->individual_summary_mapper($evaluations);

        return $results->toArray();
    }

    /**
     * Get LRP evaluations for bot performance summary (without deduplication)
     * Preserves all committee entries for LRPs with multiple committees
     */
    public function get_lrp_evaluations_for_summary($report){
        $lrpFormId = 7; // C7 is LRP form

        // Verify evaluation period exists
        if (!$report || !$report->evaluationPeriod) {
            return [];
        }

        // Get evaluations by FORM ID (not by member role) to capture all LRP evaluations
        $evaluations = $report->evaluationPeriod->assignments
            ->where('ef_id', $lrpFormId)
            ->load(['member', 'evaluator', 'form', 'evaluationPeriod', 'eval_status', 'assesment_answer.questionnaire', 'assesment_answer.ratingScaleValue'])
            ->groupBy('member_id')
            ->map(function ($memberAssignments) use ($report, $lrpFormId) {
                $member = $memberAssignments->first()->member;

                // Skip if member doesn't exist
                if (!$member) {
                    return [];
                }

                // Get unique committees this member is assigned to from the assignments
                $uniqueCommittees = $memberAssignments
                    ->unique('committee_id')
                    ->values();

                return $uniqueCommittees->map(function ($assignment) use ($member, $memberAssignments, $report) {
                    $committeeId = $assignment->committee_id;
                    $committee = Committee::find($committeeId);

                    // Skip if committee not found
                    if (!$committee && $committeeId !== null) {
                        return null;
                    }

                    // Filter assignments for this specific committee to build correct evaluation_summary
                    $committeeAssignments = $memberAssignments->filter(fn($a) => $a->committee_id == $committeeId);
                    $evaluationSummary = $this->build_evaluation_summary_from_assignments($committeeAssignments);

                    // Build full attendance structure for this specific LRP committee
                    $attendanceData = null;
                    $attendanceRecord = AttendanceAnswer::with('ratingScaleValue', 'commitee')
                        ->where('evaluation_period_id', $report->evaluationPeriod->id)
                        ->where('trustee_id', $member->id)
                        ->where('committee_id', $committeeId)
                        ->first();

                    if ($attendanceRecord) {
                        $attendance_percentage = AssesmentComputation::get_attendance_percentage(
                            $attendanceRecord->total_meetings ?? 0,
                            $attendanceRecord->total_present ?? 0
                        );

                        $attendanceArray = [[
                            'committee_id' => $attendanceRecord->committee_id,
                            'category' => $attendanceRecord->commitee?->name . ' Meetings' ?? 'Unknown Committee',
                            'total_meetings' => $attendanceRecord->total_meetings ?? 0,
                            'physically_present' => $attendanceRecord->physically_present ?? 0,
                            'considered_present' => $attendanceRecord->considered_present ?? 0,
                            'total_present' => $attendanceRecord->total_present ?? 0,
                            'attendance_percentage' => $attendance_percentage,
                            'rating_scale' => $attendanceRecord->ratingScaleValue?->value ?? 0,
                            'rating_scale_equivalent' => $attendanceRecord->ratingScaleValue?->qualitative ?? 'No Rating'
                        ]];

                        $avgRatingScale = $attendanceRecord->ratingScaleValue?->value ?? 0;
                        $attendanceSummary = [
                            'total_meetings' => $attendanceRecord->total_meetings,
                            'physically_present' => $attendanceRecord->physically_present,
                            'considered_present' => $attendanceRecord->considered_present,
                            'total_present' => $attendanceRecord->total_present,
                            'avg_attendance_percentage' => $attendance_percentage,
                            'avg_rating' => $avgRatingScale,
                            'attendance_rating_qualititative' => AssesmentComputation::get_attendance_qualitative($avgRatingScale) ?? 'No Rating'
                        ];

                        $attendanceData = [
                            'attendance' => $attendanceArray,
                            'summary' => $attendanceSummary
                        ];
                    }

                    return [
                        'member_id' => $member->id,
                        'member_name' => $member->full_name,
                        'member_lastname' => $member->last_name,
                        'member_email' => $member->email,
                        'roles' => $member->roles->pluck('name')->toArray(),
                        'committee_id' => $committeeId,
                        'committee_name' => $committee?->name ?? 'Unknown Committee',
                        'evaluation_summary' => $evaluationSummary,
                        'attendance' => $attendanceData ?? ['attendance' => [], 'summary' => ['avg_rating' => 0, 'attendance_rating_qualititative' => 'No Rating']],
                    ];
                });
            })
            ->flatten(1)
            ->filter() // Remove null values
            ->values();

        // Filter out BOT-level (null committee) assignments - LRPs should have specific committees only
        $evaluations = $evaluations->filter(fn($e) => $e['committee_id'] !== null);

        // Map through individual summary mapper WITHOUT deduplication
        return $evaluations->map(function ($evaluation) {
            $questionsByEvaluator = collect($evaluation['evaluation_summary'])
                ->flatMap(function ($evaluatorGroup) {
                    $evaluatorName = $evaluatorGroup['evaluator_name'];
                    $evaluatorID = $evaluatorGroup['evaluator_id'];

                    return collect($evaluatorGroup['evaluations'])
                        ->flatMap(fn($e) => $e['answers'])
                        ->map(fn($answer) => array_merge($answer, ['evaluator_name' => $evaluatorName,'evaluator_id' => $evaluatorID]));
                });

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
                ];
            })->values();

            $rating_average = $questionGroups->avg('average_rating');
            $total_qualitative = AssesmentComputation::get_assesment_rating_bot_summary($rating_average);
            $attendance_rating = $evaluation['attendance']['summary']['avg_rating'] ?? 0;
            $final_grade = AssesmentComputation::calculate_performance_summary($attendance_rating, $rating_average);

            return [
                'member_id' => $evaluation['member_id'],
                'member_name' => $evaluation['member_name'],
                'member_email' => $evaluation['member_email'],
                'roles' => $evaluation['roles'],
                'committee_id' => $evaluation['committee_id'],
                'committee_name' => $evaluation['committee_name'],
                'rating_average' => $rating_average,
                'total_qualitative' => $total_qualitative,
                'attendance' => $evaluation['attendance'],
                'attendance_rating' => $attendance_rating,
                'final_grade' => $final_grade
            ];
        })->values();
    }

    /**
     * Get individual results of rating for LRPs (Lead Resource Persons - C7)
     * Organized by: Committee -> Member -> Question -> Evaluators
     * LRPs appear once per committee they're assigned to
     * Attendance: Only from the specific committee they're assigned to
     * Forms: C7 (Lead Resource Person)
     */
    public function indiviual_results_of_rating_lrps_collection($report){
        $lrpFormId = 7; // C7 is LRP form

        // Verify evaluation period exists
        if (!$report || !$report->evaluationPeriod) {
            return [];
        }

        // Get evaluations by FORM ID (not by member role) to capture all LRP evaluations
        $evaluations = $report->evaluationPeriod->assignments
            ->where('ef_id', $lrpFormId)
            ->load(['member', 'evaluator', 'form', 'evaluationPeriod', 'eval_status', 'assesment_answer.questionnaire', 'assesment_answer.ratingScaleValue'])
            ->groupBy('member_id')
            ->map(function ($memberAssignments) use ($report, $lrpFormId) {
                $member = $memberAssignments->first()->member;

                // Skip if member doesn't exist
                if (!$member) {
                    return [];
                }

                // Get unique committees this member is assigned to from the assignments
                $uniqueCommittees = $memberAssignments
                    ->unique('committee_id')
                    ->values();

                return $uniqueCommittees->map(function ($assignment) use ($member, $memberAssignments, $report) {
                    $committeeId = $assignment->committee_id;
                    $committee = Committee::find($committeeId);

                    // Skip if committee not found
                    if (!$committee && $committeeId !== null) {
                        return null;
                    }

                    // Filter assignments for this specific committee to build correct evaluation_summary
                    $committeeAssignments = $memberAssignments->filter(fn($a) => $a->committee_id == $committeeId);
                    $evaluationSummary = $this->build_evaluation_summary_from_assignments($committeeAssignments);

                    // Build full attendance structure for this specific LRP committee
                    $attendanceData = null;
                    $attendanceRecord = AttendanceAnswer::with('ratingScaleValue', 'commitee')
                        ->where('evaluation_period_id', $report->evaluationPeriod->id)
                        ->where('trustee_id', $member->id)
                        ->where('committee_id', $committeeId)
                        ->first();

                    if ($attendanceRecord) {
                        $attendance_percentage = AssesmentComputation::get_attendance_percentage(
                            $attendanceRecord->total_meetings ?? 0,
                            $attendanceRecord->total_present ?? 0
                        );

                        $attendanceArray = [[
                            'committee_id' => $attendanceRecord->committee_id,
                            'category' => $attendanceRecord->commitee?->name . ' Meetings' ?? 'Unknown Committee',
                            'total_meetings' => $attendanceRecord->total_meetings ?? 0,
                            'physically_present' => $attendanceRecord->physically_present ?? 0,
                            'considered_present' => $attendanceRecord->considered_present ?? 0,
                            'total_present' => $attendanceRecord->total_present ?? 0,
                            'attendance_percentage' => $attendance_percentage,
                            'rating_scale' => $attendanceRecord->ratingScaleValue?->value ?? 0,
                            'rating_scale_equivalent' => $attendanceRecord->ratingScaleValue?->qualitative ?? 'No Rating'
                        ]];

                        $avgRatingScale = $attendanceRecord->ratingScaleValue?->value ?? 0;
                        $attendanceSummary = [
                            'total_meetings' => $attendanceRecord->total_meetings,
                            'physically_present' => $attendanceRecord->physically_present,
                            'considered_present' => $attendanceRecord->considered_present,
                            'total_present' => $attendanceRecord->total_present,
                            'avg_attendance_percentage' => $attendance_percentage,
                            'avg_rating' => $avgRatingScale,
                            'attendance_rating_qualititative' => AssesmentComputation::get_attendance_qualitative($avgRatingScale) ?? 'No Rating'
                        ];

                        $attendanceData = [
                            'attendance' => $attendanceArray,
                            'summary' => $attendanceSummary
                        ];
                    }

                    return [
                        'member_id' => $member->id,
                        'member_name' => $member->full_name,
                        'member_lastname' => $member->last_name,
                        'member_email' => $member->email,
                        'roles' => $member->roles->pluck('name')->toArray(),
                        'committee_id' => $committeeId,
                        'committee_name' => $committee?->name ?? 'Unknown Committee',
                        'evaluation_summary' => $evaluationSummary,
                        'attendance' => $attendanceData ?? ['attendance' => [], 'summary' => ['avg_rating' => 0, 'attendance_rating_qualititative' => 'No Rating']],
                    ];
                });
            })
            ->flatten(1)
            ->filter() // Remove null values
            ->values();

        // Map through individual summary mapper
        $results = $this->individual_summary_mapper($evaluations);

        return $results->toArray();
    }

    /**
     * Get individual results of rating for CO & LRPs (Corporate Officers & Lead Resource Persons)
     * DEPRECATED: Use indiviual_results_of_rating_corporate_officers_collection() and indiviual_results_of_rating_lrps_collection() instead
     * Organized by: Committee -> Member -> Question -> Evaluators
     * LRPs can appear multiple times (once per committee they're in)
     */
    public function indiviual_results_of_rating_co_and_lrps_collection($report){
        $roles = [
            'Treasurer',
            'Comptroller',
            'Corporate Secretary',
            'Lead Resource Person',
            'EVP-GM'
        ];

        $members = $this->get_members_evaluation_summary_by_roles($roles);
        $results = collect();

        foreach ($members as $member) {
            // Get member's committees through the pivot
            $user = User::find($member['member_id']);
            $memberCommittees = $user->committees()->with('committee')->get();

            if ($memberCommittees->isEmpty()) {
                // If no committee, add without committee context
                $mapped = $this->individual_summary_mapper(collect([$member]))->first();
                $results->push($mapped);
            } else {
                // Create an entry for each committee
                foreach ($memberCommittees as $committeeHasTrustee) {
                    $committee = $committeeHasTrustee->committee;

                    // Filter evaluation_summary to only evaluators from this committee
                    $committeeEvaluatorIds = $committee->committee_has_trustees()
                        ->pluck('user_id')
                        ->toArray();

                    $filteredEvaluationSummary = collect($member['evaluation_summary'])
                        ->filter(function ($evaluatorGroup) use ($committeeEvaluatorIds) {
                            return in_array($evaluatorGroup['evaluator_id'], $committeeEvaluatorIds);
                        })
                        ->values();

                    $committeeEntry = array_merge($member, [
                        'committee_id' => $committee->id,
                        'committee_name' => $committee->name,
                        'evaluation_summary' => $filteredEvaluationSummary->toArray()
                    ]);

                    $mapped = $this->individual_summary_mapper(collect([$committeeEntry]))->first();
                    $results->push($mapped);
                }
            }
        }
        return $results->values();
    }
    /**
     * Get summary of committee individual self assessment
     * Returns organized committee self assessment data for reporting
     */
    public function summary_of_committee_individual_self_assesment()
    {
        // Get detailed self-assessments (all questions with evaluators)
        $detailedAssessments = $this->committee_individual_self_assessment_collection();
        $detailedBotAssessments = $this->bot_individual_self_assessment_collection();
        $allDetailed = $detailedAssessments->concat($detailedBotAssessments);

        // Get summary assessments (aggregated by section and overall)
        $summaryAssessments = $this->committee_summary_assessment_collection();

        // Organize detailed assessments for display
        $detailedData = $allDetailed->map(function ($assessment) {
            return [
                'member_id' => $assessment['member_id'] ?? null,
                'member_name' => $assessment['member_name'],
                'committee_id' => $assessment['committee_id'] ?? null,
                'committee_name' => $assessment['committee_name'],
                'sections' => $assessment['sections']
            ];
        })->values();

        // Get crosswise summary (sections vs committees matrix)
        $crosswiseSummary = $this->committee_self_assessment_crosswise_summary($summaryAssessments);

        $result =  [
            'detailed' => $detailedData->toArray(),
            'summary' => $summaryAssessments->toArray(),
            'crosswise' => $crosswiseSummary
        ];

        return $result;
    }

    /**
     * Get crosswise summary matrix: Separated BOT and Committee sections
     * BOT sections from Form 8, Committee sections from Form 9
     */
    public function committee_self_assessment_crosswise_summary($summaryAssessments)
    {
        // Separate BOT and Committee assessments
        $botAssessment = null;
        $committeeAssessments = collect();

        foreach ($summaryAssessments as $assessment) {
            $committeeName = $assessment['committee_name'] ?? 'BOT';
            if ($committeeName === 'BOT') {
                $botAssessment = $assessment;
            } else {
                $committeeAssessments->push($assessment);
            }
        }

        // Build BOT sections matrix
        $botSections = [];
        if ($botAssessment && isset($botAssessment['sections_summary'])) {
            foreach ($botAssessment['sections_summary'] as $section) {
                $botSections[] = [
                    'section_id' => $section['section_id'],
                    'section_title' => $section['section_title'],
                    'committees' => [[
                        'committee_id' => 'bot',
                        'committee_name' => 'BOT',
                        'average_rating' => $section['average_rating'] ?? 0,
                        'qualitative' => $section['qualitative'] ?? 'No Rating'
                    ]],
                    'section_overall_average' => $section['average_rating'] ?? 0,
                    'section_overall_qualitative' => $section['qualitative'] ?? 'No Rating'
                ];
            }
        }

        // Build Committee sections matrix: Sections (rows) vs Committees (columns)
        $allCommitteeSections = collect();
        $committeeList = collect();

        // Collect all unique sections and committees
        foreach ($committeeAssessments as $assessment) {
            $committeeList->push([
                'committee_id' => $assessment['committee_id'],
                'committee_name' => $assessment['committee_name']
            ]);

            if (isset($assessment['sections_summary'])) {
                foreach ($assessment['sections_summary'] as $section) {
                    $allCommitteeSections->push([
                        'section_id' => $section['section_id'],
                        'section_title' => $section['section_title'],
                        'committee_id' => $assessment['committee_id'],
                        'committee_name' => $assessment['committee_name'],
                        'average_rating' => $section['average_rating'] ?? 0,
                        'qualitative' => $section['qualitative'] ?? 'No Rating'
                    ]);
                }
            }
        }

        // Get unique committees and sections
        $uniqueCommittees = $committeeList->unique('committee_id')->values();
        $uniqueSectionIds = $allCommitteeSections->unique('section_id')->pluck('section_id', 'section_title')->toArray();

        // Build matrix: section -> committee -> average
        $committeeSectionsMatrix = [];
        foreach ($uniqueSectionIds as $sectionTitle => $sectionId) {
            $sectionRow = [
                'section_id' => $sectionId,
                'section_title' => $sectionTitle,
                'committees' => [],
                'total_rating' => 0,
                'average_rating' => 0,
                'qualitative' => ''
            ];

            $sectionAverages = [];

            // Get average for each committee in this section
            foreach ($uniqueCommittees as $committee) {
                $committeeSection = $allCommitteeSections
                    ->where('section_id', $sectionId)
                    ->where('committee_id', $committee['committee_id'])
                    ->first();

                $average = $committeeSection['average_rating'] ?? 0;
                $qualitative = $committeeSection['qualitative'] ?? 'No Rating';

                if ($average > 0) {
                    $sectionAverages[] = $average;
                }

                $sectionRow['committees'][] = [
                    'committee_id' => $committee['committee_id'],
                    'committee_name' => $committee['committee_name'],
                    'average_rating' => $average,
                    'qualitative' => $qualitative
                ];
            }

            // Calculate section total and average
            if (!empty($sectionAverages)) {
                $sectionRow['total_rating'] = array_sum($sectionAverages);
                $sectionRow['average_rating'] = round(array_sum($sectionAverages) / count($sectionAverages), 2);
                $sectionRow['qualitative'] = AssesmentComputation::get_committee_qualitative($sectionRow['average_rating']);
            }

            $committeeSectionsMatrix[] = $sectionRow;
        }

        // Calculate overall consolidated averages
        $consolidatedData = [
            'bot_sections' => $botSections,
            'committee_sections_matrix' => $committeeSectionsMatrix,
            'committee_list' => $uniqueCommittees->toArray(),
            'overall_averages' => []
        ];

        // Per-committee overall average (including BOT)
        foreach ($summaryAssessments as $assessment) {
            $committeeName = $assessment['committee_name'] ?? 'BOT';
            $committeeId = $assessment['committee_id'] ?? 'bot';
            $overallAverage = $assessment['overall_summary']['average_rating'] ?? 0;

            $consolidatedData['overall_averages'][] = [
                'committee_id' => $committeeId,
                'committee_name' => $committeeName,
                'overall_average_rating' => $overallAverage,
                'overall_qualitative' => $assessment['overall_summary']['qualitative'] ?? 'No Rating'
            ];
        }

        // Grand consolidated average across all committees
        $allOverallAverages = array_column($consolidatedData['overall_averages'], 'overall_average_rating');
        if (!empty($allOverallAverages)) {
            $grandConsolidatedAverage = array_sum($allOverallAverages) / count($allOverallAverages);
            $consolidatedData['grand_consolidated_average'] = round($grandConsolidatedAverage, 2);
            $consolidatedData['grand_consolidated_qualitative'] = AssesmentComputation::get_committee_qualitative($grandConsolidatedAverage);
        }

        return $consolidatedData;
    }

    /**
     * Get summary results using individual collection methods
     * Consolidates BOT, CO, and LRP evaluations organized by type
     */
    public function summary_results_of_committee_assessment_collection($report){
        $collections = collect([
            [
                'id' => 1,
                'code' => 'BOT',
                'name' => 'Board of Trustees',
                'members' => []
            ],
            [
                'id' => 2,
                'code' => 'CO',
                'name' => 'Corporate Officers',
                'members' => []
            ],
            [
                'id' => 3,
                'code' => 'LRP',
                'name' => 'Lead Resource Persons',
                'members' => []
            ],
        ]);

        // Get BOT individual results
        $botMembers = $this->indiviual_results_of_rating_bot_collection();
        $collections[0]['members'] = $botMembers->map(function ($member) {
            return [
                'member_id' => $member['member_id'],
                'member_name' => $member['member_name'],
                'rating_average' => $member['rating_average'],
                'attendance_rating' => $member['attendance_rating'],
                'qualitative' => $member['total_qualitative']
            ];
        })->values();

        // Get CO/LRP individual results
        $coLrpMembers = $this->indiviual_results_of_rating_co_and_lrps_collection($report);

        // Separate CO and LRP
        $corporateOfficers = collect();
        $lrps = collect();

        foreach ($coLrpMembers as $member) {
            $user = User::find($member['member_id']);
            $roles = $user->roles()->pluck('name')->toArray();

            // Check if LRP (Form C7 - committee-only role)
            if (in_array('Lead Resource Person', $roles)) {
                $lrps->push([
                    'member_id' => $member['member_id'],
                    'member_name' => $member['member_name'],
                    'committee_id' => $member['committee_id'] ?? null,
                    'committee_name' => $member['committee_name'] ?? 'Unknown',
                    'rating_average' => $member['rating_average'],
                    'total_qualitative' => $member['total_qualitative'],
                    'attendance_rating' => $member['attendance_rating'],
                    'attendance_avg_rating' => $member['attendance']['summary']['avg_rating'] ?? 0,
                    'attendance_qualitative' => $member['attendance']['summary']['attendance_rating_qualititative'] ?? 'No Rating',
                    'final_grade_quantitative' => $member['final_grade']['quantitative'] ?? 0,
                    'final_grade_qualitative' => $member['final_grade']['qualitative'] ?? 'No Rating'
                ]);
            } else {
                // Corporate Officer - get specific role for this committee
                $committeeRoles = $user->committees()
                    ->where('committee_id', $member['committee_id'] ?? null)
                    ->with('role')
                    ->get()
                    ->pluck('role.name')
                    ->toArray();

                $roleForCommittee = !empty($committeeRoles) ? implode(', ', $committeeRoles) : 'Corporate Officer';

                $corporateOfficers->push([
                    'member_id' => $member['member_id'],
                    'member_name' => $member['member_name'],
                    'role' => $roleForCommittee,
                    'committee_id' => $member['committee_id'] ?? null,
                    'committee_name' => $member['committee_name'] ?? 'Unknown',
                    'rating_average' => $member['rating_average'],
                    'total_qualitative' => $member['total_qualitative'],
                    'attendance_rating' => $member['attendance_rating'],
                    'attendance_avg_rating' => $member['attendance']['summary']['avg_rating'] ?? 0,
                    'attendance_qualitative' => $member['attendance']['summary']['attendance_rating_qualititative'] ?? 'No Rating',
                    'final_grade_quantitative' => $member['final_grade']['quantitative'] ?? 0,
                    'final_grade_qualitative' => $member['final_grade']['qualitative'] ?? 'No Rating'
                ]);
            }
        }

        $collections[1]['members'] = $corporateOfficers->values();
        $collections[2]['members'] = $lrps->groupBy('committee_id')->map(function ($lrpGroup) {
            return [
                'committee_id' => $lrpGroup->first()['committee_id'],
                'committee_name' => $lrpGroup->first()['committee_name'],
                'members' => $lrpGroup->values()->toArray()
            ];
        })->values();
        // dd($collections);
        return $collections;
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
                'member_lastname' => $member->last_name,
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

    /**
     * Get committee individual self assessment results
     * Form 9: Committee Self Assessment
     * Organized by: Section -> Questions -> Evaluators with ratings
     */
    public function committee_individual_self_assessment_collection()
    {
        // Get all Form 9 evaluations (Committee Self Assessment)
        // Evaluates committees, not individual members
        $evaluations = TrusteeHasEvaluation::where('evaluation_id', $this->report->evaluationPeriod->id)
            ->where('ef_id', 9) // Form 9 = Committee Self Assessment
            ->with([
                'evaluator', 'evaluationPeriod', 'form', 'committee',
                'assesment_answer.questionnaire.section',
                'assesment_answer.ratingScaleValue'
            ])
            ->get()
            ->groupBy('committee_id'); // Group by committee, not member

        return $evaluations->map(function ($committeeEvals) {
            $committee = $committeeEvals->first()->committee;

            // Flatten all answers with evaluator info
            $allAnswers = collect();
            foreach ($committeeEvals as $eval) {
                foreach ($eval->assesment_answer as $answer) {
                    $allAnswers->push([
                        'section_id' => $answer->questionnaire->section->id ?? null,
                        'section_title' => $answer->questionnaire->section->title ?? 'N/A',
                        'question_id' => $answer->questionnaire_id,
                        'question' => $answer->questionnaire->name ?? 'N/A',
                        'evaluator_id' => $eval->evaluator_id,
                        'evaluator_name' => $eval->evaluator->full_name ?? 'N/A',
                        'answer_value' => $answer->ratingScaleValue?->value ?? 0,
                        'answer_qualitative' => $answer->ratingScaleValue?->qualitative ?? 'No Rating',
                        'remarks' => $answer->remarks ?? ''
                    ]);
                }
            }

            if ($allAnswers->isEmpty()) {
                return null;
            }

            // Group by section, then by question
            $sections = $allAnswers->groupBy('section_id')->map(function ($sectionAnswers) {
                $sectionTitle = $sectionAnswers->first()['section_title'];

                // Group questions within section
                $questions = $sectionAnswers->groupBy('question_id')->map(function ($questionAnswers) {
                    $firstAnswer = $questionAnswers->first();
                    $answerValues = $questionAnswers->pluck('answer_value')->filter(fn($v) => $v !== null);

                    return [
                        'question_id' => $firstAnswer['question_id'],
                        'question' => $firstAnswer['question'],
                        'total_rating' => $answerValues->sum(),
                        'average_rating' => $answerValues->count() > 0 ? round($answerValues->avg(), 2) : 0,
                        'qualitative_rating' => AssesmentComputation::get_individual_committee_qualitative(
                            $answerValues->count() > 0 ? $answerValues->avg() : 0
                        ),
                        'evaluators' => $questionAnswers->map(fn($answer) => [
                            'evaluator_id' => $answer['evaluator_id'],
                            'evaluator_name' => $answer['evaluator_name'],
                            'answer_value' => $answer['answer_value'],
                            'answer_qualitative' => $answer['answer_qualitative'],
                            'remarks' => $answer['remarks']
                        ])->values()->toArray()
                    ];
                })->values();

                // Calculate section summary
                $sectionTotalAnswers = $sectionAnswers->pluck('answer_value')->filter(fn($v) => $v !== null);
                $sectionAvg = $sectionTotalAnswers->count() > 0 ? $sectionTotalAnswers->avg() : 0;

                return [
                    'section_id' => $sectionAnswers->first()['section_id'],
                    'section_title' => $sectionTitle,
                    'questions' => $questions,
                    'section_total_rating' => $sectionTotalAnswers->sum(),
                    'section_average_rating' => $sectionAvg ? round($sectionAvg, 2) : 0,
                    'individual_summary_qualitative' => AssesmentComputation::get_individual_committee_qualitative($sectionAvg),
                    'section_qualitative' => AssesmentComputation::get_committee_qualitative($sectionAvg)
                ];
            })->values();

            // Calculate overall summary
            $allAnswerValues = $allAnswers->pluck('answer_value')->filter(fn($v) => $v !== null);
            $overallAvg = $allAnswerValues->count() > 0 ? $allAnswerValues->avg() : 0;

            return [
                'member_id' => null,
                'member_name' => $committee->name ?? 'Committee Self Assessment',
                'committee_id' => $committee->id,
                'committee_name' => $committee->name,
                'sections' => $sections->toArray(),
                'overall_total_rating' => $allAnswerValues->sum(),
                'overall_average_rating' => $overallAvg ? round($overallAvg, 2) : 0,
                'overall_individual_summary_qualitative' => AssesmentComputation::get_individual_committee_qualitative($overallAvg),
                'overall_qualitative' => AssesmentComputation::get_committee_qualitative($overallAvg)
            ];
        })->filter()->values(); // Remove null entries
    }

    /**
     * Get committee self assessment summary
     * Shows per-section and overall summary for both committee and BOT self assessments
     */
    public function committee_summary_assessment_collection()
    {
        $individualAssessments = $this->committee_individual_self_assessment_collection();

        // Add BOT self assessments (Form 8)
        $botAssessments = $this->bot_individual_self_assessment_collection();

        // Merge both collections
        $allAssessments = $individualAssessments->concat($botAssessments);

        return $allAssessments->map(function ($assessment) {
            return [
                'member_id' => $assessment['member_id'],
                'member_name' => $assessment['member_name'],
                'committee_id' => isset($assessment['committee_id']) ? $assessment['committee_id'] : 'bot',
                'committee_name' => isset($assessment['committee_id']) ? $assessment['committee_name'] : 'BOT',
                'sections_summary' => collect($assessment['sections'])->map(function ($section) {
                    return [
                        'section_id' => $section['section_id'],
                        'section_title' => $section['section_title'],
                        'total_rating' => $section['section_total_rating'],
                        'average_rating' => $section['section_average_rating'],
                        'qualitative' => $section['section_qualitative']
                    ];
                })->values()->toArray(),
                'overall_summary' => [
                    'total_rating' => $assessment['overall_total_rating'],
                    'average_rating' => $assessment['overall_average_rating'],
                    'qualitative' => $assessment['overall_qualitative'],
                    'final_grade' => AssesmentComputation::get_committee_final_grade($assessment['overall_average_rating'])
                ]
            ];
        })->values();
    }

    /**
     * Get BOT individual self assessment results
     * Form 8: BOT Self Assessment
     * Evaluates the BOT as a whole, not individual members
     */
    public function bot_individual_self_assessment_collection()
    {
        // Get all Form 8 evaluations (BOT Self Assessment)
        $evaluations = TrusteeHasEvaluation::where('evaluation_id', $this->report->evaluationPeriod->id)
            ->where('ef_id', 8) // Form 8 = BOT Self Assessment
            ->with([
                'evaluator', 'evaluationPeriod', 'form',
                'assesment_answer.questionnaire.section',
                'assesment_answer.ratingScaleValue'
            ])
            ->get();

        if ($evaluations->isEmpty()) {
            return collect();
        }

        // Flatten all answers with evaluator info
        $allAnswers = collect();
        foreach ($evaluations as $eval) {
            foreach ($eval->assesment_answer as $answer) {
                $allAnswers->push([
                    'section_id' => $answer->questionnaire->section->id ?? null,
                    'section_title' => $answer->questionnaire->section->title ?? 'N/A',
                    'question_id' => $answer->questionnaire_id,
                    'question' => $answer->questionnaire->name ?? 'N/A',
                    'evaluator_id' => $eval->evaluator_id,
                    'evaluator_name' => $eval->evaluator->full_name ?? 'N/A',
                    'answer_value' => $answer->ratingScaleValue?->value ?? 0,
                    'answer_qualitative' => $answer->ratingScaleValue?->qualitative ?? 'No Rating',
                    'remarks' => $answer->remarks ?? ''
                ]);
            }
        }

        if ($allAnswers->isEmpty()) {
            return collect();
        }

        // Group by section, then by question
        $sections = $allAnswers->groupBy('section_id')->map(function ($sectionAnswers) {
            $sectionTitle = $sectionAnswers->first()['section_title'];

            // Group questions within section
            $questions = $sectionAnswers->groupBy('question_id')->map(function ($questionAnswers) {
                $firstAnswer = $questionAnswers->first();
                $answerValues = $questionAnswers->pluck('answer_value')->filter(fn($v) => $v !== null);

                return [
                    'question_id' => $firstAnswer['question_id'],
                    'question' => $firstAnswer['question'],
                    'total_rating' => $answerValues->sum(),
                    'average_rating' => $answerValues->count() > 0 ? round($answerValues->avg(), 2) : 0,
                    'qualitative_rating' => AssesmentComputation::get_individual_committee_qualitative(
                        $answerValues->count() > 0 ? $answerValues->avg() : 0
                    ),
                    'evaluators' => $questionAnswers->map(fn($answer) => [
                        'evaluator_id' => $answer['evaluator_id'],
                        'evaluator_name' => $answer['evaluator_name'],
                        'answer_value' => $answer['answer_value'],
                        'answer_qualitative' => $answer['answer_qualitative'],
                        'remarks' => $answer['remarks']
                    ])->values()->toArray()
                ];
            })->values();

            // Calculate section summary
            $sectionTotalAnswers = $sectionAnswers->pluck('answer_value')->filter(fn($v) => $v !== null);
            $sectionAvg = $sectionTotalAnswers->count() > 0 ? $sectionTotalAnswers->avg() : 0;

            return [
                'section_id' => $sectionAnswers->first()['section_id'],
                'section_title' => $sectionTitle,
                'questions' => $questions,
                'section_total_rating' => $sectionTotalAnswers->sum(),
                'section_average_rating' => $sectionAvg ? round($sectionAvg, 2) : 0,
                'individual_summary_qualitative' => AssesmentComputation::get_individual_committee_qualitative($sectionAvg),
                'section_qualitative' => AssesmentComputation::get_committee_qualitative($sectionAvg)
            ];
        })->values();

        // Calculate overall summary
        $allAnswerValues = $allAnswers->pluck('answer_value')->filter(fn($v) => $v !== null);
        $overallAvg = $allAnswerValues->count() > 0 ? $allAnswerValues->avg() : 0;

        // Return single item collection for BOT self-assessment (evaluating BOT as a whole)
        return collect([
            [
                'member_id' => null,
                'member_name' => 'Board of Trustees',
                'committee_id' => null,
                'committee_name' => 'BOT',
                'sections' => $sections->toArray(),
                'overall_total_rating' => $allAnswerValues->sum(),
                'overall_average_rating' => $overallAvg ? round($overallAvg, 2) : 0,
                'overall_individual_summary_qualitative' => AssesmentComputation::get_individual_committee_qualitative($overallAvg),
                'overall_qualitative' => AssesmentComputation::get_committee_qualitative($overallAvg)
            ]
        ]);
    }

    private function downloadExcelReport(
        array $data,
        string $bladePath,
        string $reportName,
        array $columnWidths = []
    ) {
        $fileName = $reportName . ' (' . $data['evaluation_period'] . ').xlsx';

        if (collect($data['collections'] ?? [])->isEmpty()) {

            Notification::make()
                ->title('No data is available for this report.')
                ->warning()
                ->send();

            return redirect()->back();
        }

        return Excel::download(

            new class($data, $bladePath, $columnWidths) implements WithMultipleSheets {

                public function __construct(
                    private array $data,
                    private string $bladePath,
                    private array $columnWidths
                ) {
                }

                public function sheets(): array
                {
                    $sheets = [];

                    foreach ($this->data['collections'] as $index => $collection) {

                        $sheets[] = new class(
                            $collection,
                            $this->data,
                            $this->bladePath,
                            $this->columnWidths,
                            $index
                        ) implements FromView, WithTitle, WithColumnWidths {

                            public function __construct(
                                private $collection,
                                private array $data,
                                private string $bladePath,
                                private array $columnWidths,
                                private $index
                            ) {
                            }

                            public function title(): string
                            {
                                return $this->collection['code'] ?? $this->collection['member_lastname'] ?? 'Sheet ' . ( (int)$this->index + 1);
                            }

                            public function view(): View
                            {
                                return view($this->bladePath, [
                                    'collection' => $this->collection,
                                    'data' => $this->data,
                                ]);
                            }

                            public function columnWidths(): array
                            {
                                $widths = [];

                                foreach (range('A', 'Z') as $column) {
                                    $widths[$column] = 20;
                                }

                                $widths['A'] = 6;
                                $widths['B'] = 40;

                                return $widths;
                            }
                        };
                    }

                    return $sheets;
                }
            },

            $fileName
        );
    }

    private function downloadCommitteeAssessmentExcel(
        array $payLoad,
        string $bladePath,
        string $reportName
    ) {
        $data = $payLoad['collections'];

        $fileName = $reportName
            . ' (' . $payLoad['evaluation_period'] . ').xlsx';

        $committees = collect($data['detailed'] ?? [])
            ->pluck('committee_name')
            ->filter()
            ->unique()
            ->values();

        if ($committees->isEmpty()) {
            Notification::make()
                ->title('No committee assessment data is available for this evaluation period.')
                ->warning()
                ->send();

            return redirect()->back();
        }

        return Excel::download(

            new class(
                $payLoad,
                $bladePath,
                $committees
            ) implements WithMultipleSheets {

                public function __construct(
                    private array $data,
                    private string $bladePath,
                    private $committees
                ) {}

                public function sheets(): array
                {
                    $sheets = [];

                    foreach ($this->committees as $committeeName) {

                        $sheets[] = new class(
                            $this->data,
                            $this->bladePath,
                            $committeeName
                        ) implements FromView, WithTitle, WithColumnWidths {

                            public function __construct(
                                private array $data,
                                private string $bladePath,
                                private string $committeeName
                            ) {}

                            public function title(): string
                            {
                                return $this->committeeName;
                            }

                            public function view(): View
                            {
                                return view($this->bladePath, [
                                    'data' => $this->data,
                                    'committeeName' => $this->committeeName,
                                ]);
                            }

                            public function columnWidths(): array
                            {
                                return [
                                    'A' => 8,
                                    'B' => 45,
                                    'C' => 18,
                                    'D' => 18,
                                    'E' => 18,
                                    'F' => 18,
                                    'G' => 18,
                                    'H' => 18,
                                    'I' => 18,
                                    'J' => 18,
                                    'K' => 18,
                                    'L' => 18,
                                ];
                            }
                        };
                    }

                    return $sheets;
                }
            },

            $fileName
        );
    }
}
