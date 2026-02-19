<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\Questionnaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationC1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('evaluation_forms')->insert([
            [
                'id' => 1,
                'pdf_template_id' => 2,
                'shortcode' => 'C1',
                'title' => 'BOT EVALUATION FORM C.1 - CHAIRMAN OF THE BOARD',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 1,
                'evaluation_form_id' => 1,
                'rating_scale_id' => 1,
                'section_type_id' => 1,
                'title' => 'BOT Performance (to be rated by Members of the Governance Committee) - 70 %',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Questionnaire::insert([
            [
                'section_id' => 1,
                'name' => 'Ability to control and regulate BOT meetings.',
            ],
            [
                'section_id' => 1,
                'name' => 'Professionalism displayed during BOT deliberations.',
            ],
            [
                'section_id' => 1,
                'name' => 'Observance of proper protocol during BOT meetings.',
            ],
            [
                'section_id' => 1,
                'name' => 'Level of diligence manifested in preparing for BOT meetings.',
            ],

            [
                'section_id' => 1,
                'name' => 'Adoption of a critical yet constructive stance during deliberations of matters on hand.',
            ],
            [
                'section_id' => 1,
                'name' => 'Observe balanced consideration of the interests of various stakeholders.',
            ],
            [
                'section_id' => 1,
                'name' => 'Promotes the interest of AFPSLAI in all proposals advanced for BOT action.',
            ],

            [
                'section_id' => 1,
                'name' => 'Recognizes and respects the various viewpoints of all Board members.',
            ],

            [
                'section_id' => 1,
                'name' => 'Familiarity with the business of the Association.',
            ],
            [
                'section_id' => 1,
                'name' => 'Adopts a strategic mindset rather than a micro-management position. ',
            ]
        ]);


        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 2,
                'evaluation_form_id' => 1,
                'rating_scale_id' => 2,
                'section_type_id' => 2,
                'title' => 'Attendance in BOT Meetings (to be rated by the Corporate Secretary) - 30 %',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        AttendanceSection::insert([
            [
                'id' => 1,
                'section_id' => 2,
                'show_total_meetings' => true,
                'show_physically_present' => true,
                'show_considered_present' => true,
                'show_total_present' => true,
                'show_attendance_rating' => true,
            ]
        ]);

        AttendanceMeeting::insert([
            [
                'attendance_section_id' => 1,
                'name' => 'BOT Meeting (Special & Regular)',
            ]
        ]);

    }
}
