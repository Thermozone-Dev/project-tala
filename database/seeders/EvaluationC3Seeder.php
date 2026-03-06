<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\Questionnaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationC3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // evaluation_forms
        DB::unprepared('SET IDENTITY_INSERT evaluation_forms ON');

        DB::table('evaluation_forms')->insert([
            [
                'id' => 3,
                'pdf_template_id' => 2,
                'shortcode' => 'C3',
                'title' => 'BOT EVALUATION FORM C.3 - EVP – GM',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT evaluation_forms OFF');


        // evaluation_form_sections (first section)
        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections ON');

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 5,
                'evaluation_form_id' => 3,
                'rating_scale_id' => 1,
                'section_type_id' => 1,
                'title' => 'BOT Performance (to be rated by Members of the Governance Committee) - 70%',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections OFF');


        Questionnaire::insert([
            [
                'section_id' => 5,
                'name' => 'Active and constructive participation in BOT/ Committee meetings (can disagree without being disagreeable).'
            ],
            [
                'section_id' => 5,
                'name' => 'Professionalism displayed during BOT/ Committee discussions.'
            ],
            [
                'section_id' => 5,
                'name' => 'Competence displayed during BOT/ Committee discussions.'
            ],
            [
                'section_id' => 5,
                'name' => 'Level of diligence manifested in preparing for BOT/ Committee meetings.'
            ],
            [
                'section_id' => 5,
                'name' => 'Familiarity with company policies and operations.'
            ],
            [
                'section_id' => 5,
                'name' => 'Observance of proper protocol during BOT / Committee meetings.'
            ],
            [
                'section_id' => 5,
                'name' => 'Ability to justify the position Management is taking in relation to proposals elevated to BOT.'
            ],
            [
                'section_id' => 5,
                'name' => 'Level of clarity of understanding on the role of the EVP-GM in the Management of the Association.'
            ],
        ]);


        // evaluation_form_sections (attendance section)
        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections ON');

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 6,
                'evaluation_form_id' => 3,
                'rating_scale_id' => 2,
                'section_type_id' => 2,
                'title' => 'Attendance in BOT / Committee Meetings & other related activities (to be rated by the Corporate Secretary) - 30 %',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections OFF');


        // attendance_sections
        DB::unprepared('SET IDENTITY_INSERT attendance_sections ON');

        AttendanceSection::insert([
            [
                'id' => 3,
                'section_id' => 6,
                'show_total_meetings' => true,
                'show_physically_present' => true,
                'show_considered_present' => true,
                'show_total_present' => true,
                'show_attendance_rating' => true,
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT attendance_sections OFF');


        AttendanceMeeting::insert([
            [
                'attendance_section_id' => 3,
                'name' => 'Board Meetings (Regular & Special)',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Governance Committee',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Audit & Compliance',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Risk Oversight',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Membership',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Credit & Collection',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Compensation',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Amendment',
            ],
            [
                'attendance_section_id' => 3,
                'name' => 'Planning Sessions & other related activities',
            ],
        ]);
    }
}
