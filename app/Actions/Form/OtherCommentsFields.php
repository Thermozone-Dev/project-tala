<?php

namespace App\Actions\Form;

use App\Models\EvaluationFormSection;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Lorisleiva\Actions\Concerns\AsAction;

class OtherCommentsFields
{
    use AsAction;

    public function handle($ef_id, $trustee_id = null)
    {
        $eval_form_section = EvaluationFormSection::where('evaluation_form_id',$ef_id)
            ->where('section_type_id',3) // 3 = 'Other Comments'
            ->get();

        if ($eval_form_section->isEmpty()) return [];

        foreach ($eval_form_section as $other_comment) {

            $prefix = ($trustee_id ? 'other_comments_ans.' : '').$other_comment->id.'.';
            $fields[] = Section::make($other_comment->title)
                    ->collapsible()
                    ->schema([
                        Textarea::make($prefix.'comment')
                            ->required()
                            ->hiddenLabel()
                            ->rows(10)
                            ->validationMessages([
                                'required' => 'This field is required.',
                            ])
                    ]);
        }


        return $fields;

    }
}
