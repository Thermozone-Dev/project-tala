<?php

namespace App\Actions;

use App\Models\OtherCommentAnswer;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveOtherComments
{
    use AsAction;

    public function handle($data,$record)
    {
        foreach($data['other_comments_ans'] as $index => $answer){
            OtherCommentAnswer::updateOrCreate(
                [
                    'trustee_evaluation_id' => $record->id,
                    'comment_id' => $index,
                ],
                [
                    'comment' => $answer['comment'],
                ]
            );
        }
    }
}
