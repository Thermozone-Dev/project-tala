<?php

namespace Database\Seeders;

use App\Models\RatingScale;
use App\Models\RatingScaleValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatingScalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Rating Scales
        |--------------------------------------------------------------------------
        */

        $assessment = RatingScale::updateOrCreate(
            ['type' => 1],
            ['name' => 'Assessment']
        );

        $attendance = RatingScale::updateOrCreate(
            ['type' => 2],
            ['name' => 'Attendance']
        );

        $selfAssessment = RatingScale::updateOrCreate(
            ['type' => 3],
            ['name' => 'Self Assessment']
        );

        /*
        |--------------------------------------------------------------------------
        | Assessment Values
        |--------------------------------------------------------------------------
        */

        $assessmentValues = [
            [1, 'Satisfactory'],
            [2, 'Good'],
            [3, 'Very Good'],
            [4, 'Superior'],
            [5, 'Excellent'],
        ];

        foreach ($assessmentValues as [$value, $qualitative]) {
            RatingScaleValue::updateOrCreate(
                [
                    'rating_scale_id' => $assessment->id,
                    'value' => $value,
                ],
                [
                    'name' => null,
                    'qualitative' => $qualitative,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance Values
        |--------------------------------------------------------------------------
        */

        $attendanceValues = [
            [1, 'Less than 40% of the time', 'Satisfactory'],
            [2, '40% to less than 60% of the time', 'Good'],
            [3, '60% to less than 80% of the time', 'Very Good'],
            [4, '80% to less than 100% of the time', 'Superior'],
            [5, '100% attendance', 'Excellent'],
        ];

        foreach ($attendanceValues as [$value, $name, $qualitative]) {
            RatingScaleValue::updateOrCreate(
                [
                    'rating_scale_id' => $attendance->id,
                    'value' => $value,
                ],
                [
                    'name' => $name,
                    'qualitative' => $qualitative,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Self Assessment Values
        |--------------------------------------------------------------------------
        */

        $selfValues = [
            [1, 'Strongly Disagree'],
            [2, 'Somewhat Disagree'],
            [3, 'Somewhat Agree'],
            [4, 'Strongly Agree'],
        ];

        foreach ($selfValues as [$value, $qualitative]) {
            RatingScaleValue::updateOrCreate(
                [
                    'rating_scale_id' => $selfAssessment->id,
                    'value' => $value,
                ],
                [
                    'name' => null,
                    'qualitative' => $qualitative,
                ]
            );
        }
    }

}
