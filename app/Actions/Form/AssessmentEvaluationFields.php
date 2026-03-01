<?php

namespace App\Actions\Form;

use App\Models\EvaluationFormSection;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Lorisleiva\Actions\Concerns\AsAction;

class AssessmentEvaluationFields
{
    use AsAction;

    public function handle($ef_id)
    {
        $eval_form_section = EvaluationFormSection::where('evaluation_form_id',$ef_id)
            ->where('section_type_id',1) // 1 = 'Assessment'
            ->first();

        $rating_scale_values = $eval_form_section->ratingScale->values
            ->mapWithKeys(function ($item) {
                return [
                    $item->value => $item->value . ' - ' . $item->qualitative
                ];
            })
            ->toArray();

        foreach ($eval_form_section->questionnaires as $question) {
            $fields[] = Select::make($question->id)
                ->inlineLabel()
                ->label($question->name)
                ->options($rating_scale_values);
        }

        foreach ($rating_scale_values as $index => $rating_scale_value) {
            $rating_scales[] = Placeholder::make('rating_scale_'.$index)->label($rating_scale_value);
        }

        return [
            Section::make('Rating Scale')->columns(count($rating_scale_values))->schema($rating_scales),
            Section::make($eval_form_section->title)->schema($fields)
        ];
    }
}
