<?php

namespace App\Actions;

use App\Models\AttendanceAnswer;
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

    public static function calculate_attendance_rating_per_member($member_id, $evaluation_period_id = null, $commitee_id = null){
        $attendance =  AttendanceAnswer::where('trustee_id',$member_id)
                ->when(($commitee_id), function ($q) use ($commitee_id){
                    return $q->where('committee_id',$commitee_id);
                })
                ->when(($evaluation_period_id), function ($q) use ($evaluation_period_id){
                    return $q->where('evaluation_period_id',$evaluation_period_id);
                })
                ->get();

        $percentage = self::get_attendance_percentage($attendance->sum('total_meetings'), $attendance->sum('total_present'));
        $rating  = self::get_attendance_rating($percentage);
        $attendance = [
            'total_meetings' => $attendance->sum('total_meetings'),
            'total_present' => $attendance->sum('total_present'),
            'average_grade' => $attendance->avg(fn($a) => $a->ratingScaleValue?->value ?? 0),
            'attendance_percentage' =>  $percentage,
            'rating' => $rating?->qualitative ?? 'Rating not found'
        ];

        return $attendance;

    }

    public static function calculate_performance_summary($attendance_avg = 0, $assesment_avg = 0){
        // Total: 70% assessment + 30% attendance
        $total_quantitative = null;
        if ($assesment_avg !== null && $attendance_avg !== null) {
            $total_quantitative = ($assesment_avg * 0.70) + ($attendance_avg * 0.30);
        }
        $total_qualitative = self::get_assesment_rating_bot_summary($total_quantitative);

        return [
            'quantitative' => $total_quantitative,
            'qualitative' => $total_qualitative,
        ];
    }


    public static function get_assesment_rating_bot_summary($value)
    {
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
