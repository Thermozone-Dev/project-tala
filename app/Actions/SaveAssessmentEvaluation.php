<?php

namespace App\Actions;

use App\Models\OtherCommentAnswer;
use App\Models\QuestionaireAnswer;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveAssessmentEvaluation
{
    use AsAction;

    public function handle($data,$record)
    {
        foreach($data['assesment_answer'] as $index => $answer){
            QuestionaireAnswer::updateOrCreate(
                [
                    'trustee_evaluation_id' => $record->id,
                    'questionnaire_id' => $index
                ],
                [
                    'rating_scale_values_id' => $answer['rating_scale_values_id'],
                    'remarks' => $answer['remarks'] ?? null
                ]
            );
        }
    }
}
