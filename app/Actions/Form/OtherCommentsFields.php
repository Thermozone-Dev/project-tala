<?php

namespace App\Actions\Form;

use App\Models\EvaluationFormSection;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Lorisleiva\Actions\Concerns\AsAction;

class OtherCommentsFields
{
    use AsAction;

    public function handle($ef_id)
    {
        $eval_form_section = EvaluationFormSection::where('evaluation_form_id',$ef_id)
            ->where('section_type_id',3) // 3 = 'Other Comments'
            ->first();

        if (!$eval_form_section) return [];

        return [
            Section::make($eval_form_section->title)
                ->collapsible()
                ->schema([
                    Textarea::make('other_comments')
                        ->required()
                        ->hiddenLabel()
                        ->rows(10)
                        ->validationMessages([
                            'required' => 'This field is required.',
                        ])
                ])
        ];
    }
}
