<?php

namespace App\Actions\Form;

use App\Models\EvaluationFormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\FontWeight;
use Lorisleiva\Actions\Concerns\AsAction;

class AssessmentEvaluationFields
{
    use AsAction;

    public function handle($ef_id)
    {
        $eval_form_sections = EvaluationFormSection::where('evaluation_form_id',$ef_id)
            ->where('section_type_id',1) // 1 = 'Assessment'
            ->get();

        if ($eval_form_sections->isEmpty()) return [];

        foreach ($eval_form_sections as $index => $eval_form_section) {
            if($index == 0){
                $rating_scale_values = $eval_form_section->ratingScale->values
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->value => $item->value . ' - ' . $item->qualitative,
                        ];
                    })
                    ->toArray();

                foreach ($rating_scale_values as $rating_scale_value) {
                    $rating_scales[] = Text::make($rating_scale_value)->weight(FontWeight::Bold)->color('neutral');
                }

                $sections[] = Section::make('Rating Scale')->columns(count($rating_scale_values))->schema($rating_scales);
            }

            foreach ($eval_form_section->questionnaires as $index2 => $question) {

                if($eval_form_section->add_remarks){
                    $fields[] = Grid::make(4)->schema([
                        Text::make($question->name)
                            ->columnSpan(2)
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                        Select::make($question->id)
                            ->required()
                            ->hiddenLabel()
                            ->validationMessages([
                                'required' => 'This field is required.',
                            ])
                            ->options($rating_scale_values),
                        Textarea::make('remarks_'.$index2)
                            ->placeholder('Remarks...')
                            ->hiddenLabel()
                    ]);

                }else{
                    $fields[] = Grid::make(3)->schema([
                        Text::make($question->name)
                            ->columnSpan(2)
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                        Select::make($question->id)
                            ->required()
                            ->hiddenLabel()
                            ->validationMessages([
                                'required' => 'This field is required.',
                            ])
                            ->options($rating_scale_values),
                    ]);
                }

            }

            $sections[] = Section::make($eval_form_section->title)->schema($fields)->collapsible();

            $fields = [];

        }

        return $sections;
    }
}
