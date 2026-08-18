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
        $assesment_grade = ($assesment_avg ?? 0) * 0.70;
        $attendance_grade = ($attendance_avg ?? 0) * 0.30;
        $total_quantitative = $assesment_grade + $attendance_grade;
        $total_qualitative = self::get_assesment_rating_bot_summary($total_quantitative);

        return [
            'quantitative' => $total_quantitative,
            'qualitative' => $total_qualitative,
            'assesment_grade' => $assesment_grade ?? 0,
            'attendance_grade' => $attendance_grade ?? 0,
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

    /**
     * Get committee final grade (qualitative only)
     * For committee self assessments with no attendance component
     *
     * @param float $average_rating The average rating value
     * @return array Array with quantitative and qualitative rating
     */
    public static function get_committee_final_grade($average_rating = 0)
    {
        $qualitative = self::get_committee_qualitative($average_rating);

        return [
            'quantitative' => round($average_rating, 2),
            'qualitative' => $qualitative
        ];
    }



    public static function get_committee_qualitative($value)
    {
        $rating = [
            'Exceptional' => [3.50, 4.00],
            'Superior' => [3.00, 3.49],
            'Satisfactory' => [2.50, 3.49],
            'Needs Improvement' => [2.00, 2.49],
            'Unsatisfactory' =>[0, 1.99]
        ];

        foreach ($rating as $index => [$min, $max]) {
            if ($value >= $min && $value <= $max) {
                return $index;
            }
        }

        return 'No Qualitative Found';

    }

     public static function get_individual_committee_qualitative($value)
    {
        $rating = [
            'Strongly Disagree' => [0, 1.00],
            'Somewhat Disagree' => [1.01, 2.00],
            'Somewhat Agree' => [2.01, 3.00],
            'Strongly Agree' => [3.01, 4.00],
        ];

        foreach ($rating as $index => [$min, $max]) {
            if ($value >= $min && $value <= $max) {
                return $index;
            }
        }

        return 'No Qualitative Found';

    }

    /**
     * Get attendance qualitative rating based on quantitative value (0-5 scale)
     * Attendance Rating Ranges:
     * 4.01 to 5 = Excellent
     * 3.01 to 4 = Superior
     * 2.01 to 3 = Very Good
     * 1.01 to 2 = Good
     * 0 to 1 = Satisfactory
     */
    public static function get_attendance_qualitative($value)
    {
        $rating = [
            'Excellent' => [4.01, 5.00],
            'Superior' => [3.01, 4.00],
            'Very Good' => [2.01, 3.00],
            'Good' => [1.01, 2.00],
            'Satisfactory' => [0, 1.00]
        ];

        foreach ($rating as $index => [$min, $max]) {
            if ($value >= $min && $value <= $max) {
                return $index;
            }
        }

        return 'No Rating';
    }



}
