<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\Questionnaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationC4Seeder extends Seeder
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
                'id' => 4,
                'pdf_template_id' => 2,
                'shortcode' => 'C4',
                'title' => 'BOT EVALUATION FORM C.4 - Corporate Secretary',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::unprepared('SET IDENTITY_INSERT evaluation_forms OFF');


        // evaluation_form_sections (performance section)
        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections ON');

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 7,
                'evaluation_form_id' => 4,
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
            ['section_id' => 7, 'name' => 'Accuracy of Board and Committee meeting minutes.'],
            ['section_id' => 7, 'name' => 'Quality of agenda line up for the scheduled Board / Committee meeting.'],
            ['section_id' => 7, 'name' => 'Timely distribution of Board and Committee folders.'],
            ['section_id' => 7, 'name' => 'Mastery of the provisions of the Amended By-laws.'],
            ['section_id' => 7, 'name' => 'Familiarity with past resolutions and matters of discussion by the Board.'],
            ['section_id' => 7, 'name' => 'Quality of legal advice given to the Board as needed*'],
            ['section_id' => 7, 'name' => 'Familiarity with laws and regulations that govern the operations of AFPSLAI as an NSSLA.'],
            ['section_id' => 7, 'name' => 'Efficiency in handling annual BOT assessments.'],
            ['section_id' => 7, 'name' => 'Handling of proceedings during Annual Membership Meetings.'],
            ['section_id' => 7, 'name' => 'Handling of records of Board and Committee meetings.'],
            ['section_id' => 7, 'name' => 'Observance of proper protocol during Board/ Committee meetings.'],
            ['section_id' => 7, 'name' => 'Level of clarity of understanding on the role of the Corporate Secretary.'],
        ]);


        // evaluation_form_sections (attendance section)
        DB::unprepared('SET IDENTITY_INSERT evaluation_form_sections ON');

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 8,
                'evaluation_form_id' => 4,
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
                'id' => 4,
                'section_id' => 8,
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
                'attendance_section_id' => 4,
                'name' => 'BOT Meeting (Special & Regular)',
            ]
        ]);
    }
}
