<?php

namespace App\Actions\Form;

use App\Models\EvaluationFormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Filament\Schemas\Components\Text;


class AttendanceEvaluationFields
{
    use AsAction;

    public function handle($ef_id, $trustee_id = null)
    {
        $eval_form_section = EvaluationFormSection::where('evaluation_form_id',$ef_id)
            ->where('section_type_id',2) // 2 = 'Attendance'
            ->first();

        if (!$eval_form_section) return [];

        $rating_scale_values = $eval_form_section->ratingScale->values->reverse();

        foreach ($rating_scale_values as $rating_scale_value) {
            $rating_scales[] =
                Grid::make()
                    ->schema([
                        Text::make($rating_scale_value->name)
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                        Text::make($rating_scale_value->value.' - '.$rating_scale_value->qualitative)
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                    ]);
        }

        $sections[] = Section::make('Rating Scale')->columns(1)->collapsible()->schema($rating_scales);

        $columns = [
            'show_total_meetings',
            'show_physically_present',
            'show_considered_present',
            'show_total_present',
            'show_attendance_rating',
        ];

        $is_true_columns = collect($eval_form_section->attendanceSection->only($columns))->filter()->keys()
            ->map(function ($column) {
                return Str::after($column, 'show_');
            });

        $columns_count = count($is_true_columns);

        $heading[] = Text::make('')->columnSpan(2);

        foreach ($is_true_columns as $column) {
            $heading[] = Text::make(Str::headline($column))->weight(FontWeight::Bold)->color('neutral');
        }

        foreach ($eval_form_section->attendanceSection->meetings as $meeting) {
            $prefix = ($trustee_id ? 'attendance_answer.' : '').$meeting->id.'.';

            $fields[] =  Text::make($meeting->name)->columnSpan(2)->weight(FontWeight::Bold)->color('neutral');
            foreach ($is_true_columns as $column) {
                if($column == 'attendance_rating'){
                    $fields[] = Select::make($prefix.$column)
                    // ->required()
                    ->hiddenLabel()
                    ->validationMessages([
                        'required' => 'This field is required.',
                    ])
                    ->options($rating_scale_values->pluck('qualitative','id')->toArray());
                    continue;
                }
                $fields[] = TextInput::make($prefix.$column)
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->extraInputAttributes(['min' => 0])
                    ->maxValue(300)
                    ->nullable()
                    ->hiddenLabel()
                    ->validationMessages([
                        'required' => 'This field is required.',
                    ]);
            }
        }

        $sections[] = Section::make($eval_form_section->title)->columns($columns_count+2)->schema(array_merge($heading,$fields))->collapsible();

        return $sections;
    }
}
