<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\Questionnaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationC6Seeder extends Seeder
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
                'id' => 6,
                'pdf_template_id' => 2,
                'shortcode' => 'C6',
                'title' => 'BOT EVALUATION FORM C.6 - Corporate Comptroller',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT evaluation_forms OFF');


        // evaluation_form_sections (performance section)
        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections ON');

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 11,
                'evaluation_form_id' => 6,
                'rating_scale_id' => 1,
                'section_type_id' => 1,
                'title' => 'Performance of Role as Corporate Officer - 70 %',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections OFF');


        Questionnaire::insert([
            ['section_id' => 11, 'name' => 'Mastery of the financial records of the Association.'],
            ['section_id' => 11, 'name' => 'Ability to communicate financial information in a manner that can be easily understood by the Board.'],
            ['section_id' => 11, 'name' => 'Familiarity with past resolutions and matters of discussion by the Board on matters relating to the financial affairs of the Association.'],
            ['section_id' => 11, 'name' => 'Quality of technical advice given to the Board / Committee as needed or solicited.'],
            ['section_id' => 11, 'name' => 'Familiarity with laws and regulations that govern the reporting and management of financial affairs of the Association.'],
            ['section_id' => 11, 'name' => 'Professionalism displayed during BOT / Committee discussions.'],
            ['section_id' => 11, 'name' => 'Level of diligence manifested in preparing for BOT / Committee meetings.'],
            ['section_id' => 11, 'name' => 'Adoption of a critical yet constructive stance during deliberations of matters on hand.'],
            ['section_id' => 11, 'name' => 'Observance of proper protocol during Board / Committee meetings.'],
            ['section_id' => 11, 'name' => 'Level of clarity of understanding on the role of the Corporate Comptroller.'],
        ]);


        // evaluation_form_sections (attendance section)
        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections ON');

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 12,
                'evaluation_form_id' => 6,
                'rating_scale_id' => 2,
                'section_type_id' => 2,
                'title' => 'Attendance in BOT / Committee Meetings & other related activities (to be rated by the Head, OBS, Chairman’s Office in lieu of the Corporate Secretary) - 30 %',
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
                'id' => 6,
                'section_id' => 12,
                'show_total_meetings' => true,
                'show_physically_present' => true,
                'show_considered_present' => true,
                'show_total_present' => true,
                'show_attendance_rating' => true,
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT attendance_sections OFF');


        AttendanceMeeting::insert([
            ['attendance_section_id' => 6, 'name' => 'Board Meetings (Regular & Special)'],
            ['attendance_section_id' => 6, 'name' => 'Governance Committee'],
            ['attendance_section_id' => 6, 'name' => 'Audit & Compliance'],
            ['attendance_section_id' => 6, 'name' => 'Risk Oversight'],
            ['attendance_section_id' => 6, 'name' => 'Membership'],
            ['attendance_section_id' => 6, 'name' => 'Credit & Collection'],
            ['attendance_section_id' => 6, 'name' => 'Amendment'],
            ['attendance_section_id' => 6, 'name' => 'Compensation'],
            ['attendance_section_id' => 6, 'name' => 'Planning Sessions & other related activities'],
        ]);
    }
}
