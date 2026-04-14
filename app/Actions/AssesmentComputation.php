<?php

namespace App\Actions;

use App\Models\RatingScaleValue;
use InvalidArgumentException;

class AssesmentComputation
{

    public static function get_attendance_percentage($total_meetings = 0, $present = 0)
    {
        if ($total_meetings < 0 || $present < 0) {
            throw new InvalidArgumentException('Values cannot be negative.');
        }

        if ($present > $total_meetings) {
            throw new InvalidArgumentException('Present cannot exceed total meetings.');
        }

        if ($total_meetings == 0) {
            return 0;
        }

        return (int) round(($present / $total_meetings) * 100);
    }


    public static function get_attendance_rating($percentage)
    {
        // dd($percentage);
        $percentage = max(0, min(100, (int) $percentage));

        $rating = [
            1 => [0, 39],
            2 => [40, 59],
            3 => [60, 79],
            4 => [80, 99],
            5 => [100, 100],
        ];

        foreach ($rating as $index => [$min, $max]) {
            if ($percentage >= $min && $percentage <= $max) {
                $rating = RatingScaleValue::where('rating_scale_id', 2)->where('value', $index)->first();
                if(!$rating) {
                    return null;
                }

                return $rating;
            }
        }

        return null;

    }



    public static function get_assesment_rating_bot_summary($value)
    {
        // dd($percentage);
        // $percentage = max(0, min(100, (int) $rating));

        $rating = [
            'Excellent' => [4.50, 5.00],
            'Superior' => [3.50, 4.49],
            'Very Good' => [2.50, 3.49],
            'Good' => [1.50, 2.49],
            'Satisfactory' =>[0, 1.49]
        ];

        foreach ($rating as $index => [$min, $max]) {
            if ($value >= $min && $value <= $max) {
                return $index;
            }
        }

        return 'No Qualitative Found';

    }




}
