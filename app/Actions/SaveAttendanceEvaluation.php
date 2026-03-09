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
            $answer['attendance_rating_scale_values_id'] = $answer['attendance_rating'];
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
