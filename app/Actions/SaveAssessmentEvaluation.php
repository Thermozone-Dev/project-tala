<?php

namespace App\Actions;

use App\Models\OtherCommentAnswer;
use App\Models\QuestionaireAnswer;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveAssessmentEvaluation
{
    use AsAction;

    public function handle($data, $record)
    {
        foreach($data['assesment_answer'] as $index => $answer){
            $ratingScaleValuesId = $answer['rating_scale_values_id'] ?? null;
            $remarks = $answer['remarks'] ?? null;

            $existing = QuestionaireAnswer::where([
                'trustee_evaluation_id' => $record->id,
                'questionnaire_id' => $index
            ])->first();

            // Skip only if no existing record and both values are empty
            if(!$existing && is_null($ratingScaleValuesId) && is_null($remarks)){
                continue;
            }

            QuestionaireAnswer::updateOrCreate(
                [
                    'trustee_evaluation_id' => $record->id,
                    'questionnaire_id' => $index
                ],
                $answer
            );
        }
    }
}
