<?php

namespace App\Actions;

use App\Models\AttendanceAnswer;
use App\Models\EvaluationFormSection;
use App\Models\QuestionaireAnswer;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckEvaluationCompleted
{
    use AsAction;

    public function handle($ef_id,$trustee_eval_id)
    {
        // Attendance
        $attendance_section = EvaluationFormSection::where('evaluation_form_id', $ef_id)
            ->where('section_type_id', 2) // 2 = 'Attendance'
            ->first();
        $attendance = null;

        if ($attendance_section) {
            $columns = [
                'show_total_meetings',
                'show_physically_present',
                'show_considered_present',
                'show_total_present',
                'show_attendance_rating',
            ];

            $is_true_columns = collect($attendance_section->attendanceSection->only($columns))->filter()->keys()
                ->map(function ($column) {
                    return Str::after($column, 'show_');
                });

            $columns_count = count($is_true_columns);

            $total_attendance_count = $columns_count * count($attendance_section->attendanceSection->meetings);

            $attendances = AttendanceAnswer::where('trustee_evaluation_id', $trustee_eval_id)->get();

            $total_attendance_answer_count = $attendances->sum(function ($attendance) {
                return collect([
                    $attendance->total_meetings,
                    $attendance->physically_present,
                    $attendance->considered_present,
                    $attendance->total_present,
                    $attendance->attendance_rating_scale_values_id,
                ])->filter(fn($value) => !is_null($value))->count();
            });

            if ($total_attendance_answer_count != $total_attendance_count) {
                $attendance = 0;
            } else {
                $attendance = 1;
            }
        }

        // Assessment
        $assessment_section = EvaluationFormSection::where('evaluation_form_id', $ef_id)
            ->where('section_type_id', 1) // 1 = 'Assessment'
            ->get();

        $assessment = null;

        if (!$assessment_section->isEmpty()) {
            $total_questionnaires_count = EvaluationFormSection::where('evaluation_form_id', $ef_id)
                ->where('section_type_id', 1)
                ->withCount('questionnaires')
                ->get()
                ->sum('questionnaires_count');

            $total_answers_count = QuestionaireAnswer::where('trustee_evaluation_id', $trustee_eval_id)->whereNotNull('rating_scale_values_id')->count();

            if ($total_questionnaires_count != $total_answers_count) {
                $assessment = 0;
            } else {
                $assessment = 1;
            }

        }

        return match (true) {
            $attendance !== null && $assessment !== null => $attendance === 1 && $assessment === 1,
            $assessment !== null => $assessment === 1,
            $attendance !== null => $attendance === 1,
            default => false,
        };
    }
}
