<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\Questionnaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationC2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('evaluation_forms')->insert([
            [
                'id' => 2,
                'pdf_template_id' => 2,
                'shortcode' => 'C2',
                'title' => 'BOT EVALUATION FORM C.2 - TRUSTEES',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 3,
                'evaluation_form_id' => 2,
                'rating_scale_id' => 1,
                'section_type_id' => 1,
                'title' => 'BOT Performance (to be rated by Members of the Governance Committee) - 70% ',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Questionnaire::insert([
            [
                'section_id' => 3,
                'name' => 'Active and constructive participation in BOT Committee meetings (can disagree without being disagreeable)',
            ],
            [
                'section_id' => 3,
                'name' => 'Professionalism displayed during BOT/Committee discussions',
            ],
            [
                'section_id' => 3,
                'name' => 'Competence displayed during BOT/Committee discussions',
            ],
            [
                'section_id' => 3,
                'name' => 'Level of diligence manifested in preparing for BOT/Committee meetings.',
            ],
            [
                'section_id' => 3,
                'name' => 'Adoption of a critical yet constructive stance during deliberations of matters on hand.',
            ],
            [
                'section_id' => 3,
                'name' => 'Observance of proper protocol during BOT/Committee meetings.',
            ],
            [
                'section_id' => 3,
                'name' => 'Promotes the interest of AFPSLAI in all proposals advanced for BOT/Committee action.',
            ],
            [
                'section_id' => 3,
                'name' => 'Promotes the interests of the sector being represented in all proposals advanced for BOT/ Management Action.',
            ],
            [
                'section_id' => 3,
                'name' => 'Comes to meetings on time and finishes scheduled Board/ Committee meetings.',
            ],
            [
                'section_id' => 3,
                'name' => 'Adopts a strategic mindset rather than a micro-management position.',
            ],
            [
                'section_id' => 3,
                'name' => 'Respect given to fellow Trustees/ committee members (does not tend to monopolize discussion).',
            ],
            [
                'section_id' => 3,
                'name' => "Level of clarity of understanding on the role of each Committee (does not infringe on other committee’s functions).",
            ],

        ]);

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 4,
                'evaluation_form_id' => 2,
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
                'id' => 2,
                'section_id' => 4,
                'show_total_meetings' => true,
                'show_physically_present' => true,
                'show_considered_present' => true,
                'show_total_present' => true,
                'show_attendance_rating' => true,
            ]
        ]);

        AttendanceMeeting::insert([
            [
                'attendance_section_id' => 2,
                'name' => 'BOT Meeting (Special & Regular)',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'Governance Committee',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'Audit & Compliance',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'Risk Oversight',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'Human Resource',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'Credit& Collection',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'Membership & Amendment',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'ITSC',
            ],
            [
                'attendance_section_id' => 2,
                'name' => 'Planning Sessions & other related activities',
            ]
        ]);

    }
}
