<?php

namespace Database\Seeders;

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
        // rating_scales
        DB::unprepared('SET IDENTITY_INSERT rating_scales ON');

        DB::table('rating_scales')->insert([
            [
                'id' => 1,
                'type' => 1, // assessment
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'type' => 2, // attendance
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'type' => 3, // self-assessment
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT rating_scales OFF');


        // rating_scale_values
        DB::unprepared('SET IDENTITY_INSERT rating_scale_values ON');

        // Assessment
        DB::table('rating_scale_values')->insert([
            ['id' => 1, 'rating_scale_id' => 1, 'name' => null, 'value' => 1, 'qualitative' => 'Satisfactory', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'rating_scale_id' => 1, 'name' => null, 'value' => 2, 'qualitative' => 'Good', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'rating_scale_id' => 1, 'name' => null, 'value' => 3, 'qualitative' => 'Very Good', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'rating_scale_id' => 1, 'name' => null, 'value' => 4, 'qualitative' => 'Superior', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'rating_scale_id' => 1, 'name' => null, 'value' => 5, 'qualitative' => 'Excellent', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Attendance
        DB::table('rating_scale_values')->insert([
            ['id' => 6, 'rating_scale_id' => 2, 'name' => 'Less than 40% of the time', 'value' => 1, 'qualitative' => 'Satisfactory', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'rating_scale_id' => 2, 'name' => '40% to less than 60% of the time', 'value' => 2, 'qualitative' => 'Good', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'rating_scale_id' => 2, 'name' => '60% to less than 80% of the time', 'value' => 3, 'qualitative' => 'Very Good', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'rating_scale_id' => 2, 'name' => '80% to less than 100% of the time', 'value' => 4, 'qualitative' => 'Superior', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'rating_scale_id' => 2, 'name' => '100% attendance', 'value' => 5, 'qualitative' => 'Excellent', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Self-Assessment
        DB::table('rating_scale_values')->insert([
            ['id' => 11, 'rating_scale_id' => 3, 'name' => null, 'value' => 1, 'qualitative' => 'Strongly Disagree', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'rating_scale_id' => 3, 'name' => null, 'value' => 2, 'qualitative' => 'Somewhat Disagree', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'rating_scale_id' => 3, 'name' => null, 'value' => 3, 'qualitative' => 'Somewhat Agree', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'rating_scale_id' => 3, 'name' => null, 'value' => 4, 'qualitative' => 'Strongly Agree', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::unprepared('SET IDENTITY_INSERT rating_scale_values OFF');
    }
}
