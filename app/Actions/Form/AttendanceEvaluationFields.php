<?php

namespace App\Actions\Form;

use App\Models\EvaluationFormSection;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class AttendanceEvaluationFields
{
    use AsAction;

    public function handle($ef_id)
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
                        Placeholder::make('placeholder')->label($rating_scale_value->name),
                        Placeholder::make('placeholder')->label($rating_scale_value->value.' - '.$rating_scale_value->qualitative),
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

        // >>> Display Heading
        $heading[] = Placeholder::make('placeholder')->label('placeholder')->hiddenLabel()->columnSpan(2);

        foreach ($is_true_columns as $column) {
                $heading[] = Placeholder::make('placeholder')->label(Str::headline($column));
        }
        // <<< Display Heading

        foreach ($eval_form_section->attendanceSection->meetings as $meeting) {

            $fields[] =  Placeholder::make('placeholder')->label($meeting->name)->columnSpan(2);

            foreach ($is_true_columns as $column) {

                $fields[] = Select::make('meeting_'.$meeting->id.'_'.$column)
                    ->required()
                    ->hiddenLabel()
                    ->validationMessages([
                        'required' => 'This field is required.',
                    ])
                    ->options($rating_scale_values->pluck('qualitative','id')->toArray());
            }
        }

        $sections[] = Section::make($eval_form_section->title)->columns($columns_count+2)->schema(array_merge($heading,$fields))->collapsible();

        return $sections;
    }
}
