<?php

namespace App\Actions;

use App\Models\AttendanceAnswer;
use App\Models\OtherCommentAnswer;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveAttendanceEvaluation
{
    use AsAction;

    public function handle($data,$record)
    {
        foreach($data['attendance_answer'] as $index => $answer){

            $total_meetings = $answer['total_meetings'] ?? null;
            $physically_present = $answer['physically_present'] ?? null;
            $considered_present = $answer['considered_present'] ?? null;
            $total_present = $answer['total_present'] ?? null;
            $attendance_rating = $answer['attendance_rating'] ?? null;

            $existing = AttendanceAnswer::where([
                'trustee_evaluation_id' => $record->id,
                'meeting_id' => $index,
            ])->first();

            // Skip only if no existing record and attendance rating is empty
            if(!$existing && is_null($attendance_rating) && is_null($considered_present) && is_null($total_present) && is_null($physically_present) && is_null($total_meetings)){
                continue;
            }

            $answer['attendance_rating_scale_values_id'] =  $attendance_rating;

            AttendanceAnswer::updateOrCreate(
                [
                    'trustee_evaluation_id' => $record->id,
                    'meeting_id' => $index,
                ],
                $answer,
            );
        }
    }
}
