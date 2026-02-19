<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\Questionnaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationC5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('evaluation_forms')->insert([
            [
                'id' => 5,
                'pdf_template_id' => 2,
                'shortcode' => 'C5',
                'title' => 'BOT EVALUATION FORM C.5 - Corporate Treasurer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 9,
                'evaluation_form_id' => 5,
                'rating_scale_id' => 1,
                'section_type_id' => 1,
                'title' => 'Performance of Role as Corporate Officer - 70 %',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Questionnaire::insert([
            [
                'section_id' => 9,
                'name' => 'Familiarity with past resolutions and matters of discussion by the Board on matters relating to fund and treasury management.'
            ],
            [
                'section_id' => 9,
                'name' => 'Quality of technical advice given to the Board / Committee as needed or solicited.'
            ],
            [
                'section_id' => 9,
                'name' => 'Familiarity with laws and regulations that govern the management of the treasury of AFPSLAI as an NSSLA.'
            ],
            [
                'section_id' => 9,
                'name' => 'Professionalism displayed during BOT / Committee discussions.'
            ],
            [
                'section_id' => 9,
                'name' => 'Level of competence and mastery of the treasury affairs of the Association.'
            ],
            [
                'section_id' => 9,
                'name' => 'Level of diligence manifested in preparing for BOT / Committee meetings.'
            ],
            [
                'section_id' => 9,
                'name' => 'Observance of proper protocol during Board / Committee meetings.'
            ],
            [
                'section_id' => 9,
                'name' => 'Level of clarity of understanding on the role of the Corporate Treasurer.'
            ],
        ]);



        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 10,
                'evaluation_form_id' => 5,
                'rating_scale_id' => 2,
                'section_type_id' => 2,
                'title' => 'Attendance in BOT / Committee Meetings & other related activities (to be rated by the Head, OBS, Chairman’s Office in lieu of the Corporate Secretary) - 30 %',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        AttendanceSection::insert([
            [
                'id' => 5,
                'section_id' => 10,
                'show_total_meetings' => true,
                'show_physically_present' => true,
                'show_considered_present' => true,
                'show_total_present' => true,
                'show_attendance_rating' => true,
            ]
        ]);

        AttendanceMeeting::insert([
            [
                'attendance_section_id' => 5,
                'name' => 'Board Meetings (Regular & Special)',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Governance Committee',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Audit & Compliance',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Risk Oversight',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Membership',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Credit & Collection',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Amendment',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Compensation',
            ],
            [
                'attendance_section_id' => 5,
                'name' => 'Planning Sessions & other related activities',
            ],
        ]);

    }
}
