<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\Questionnaire;
use Illuminate\Support\Facades\DB;

class BOARDSelfAssesmentFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('evaluation_forms')->insert([
            [
                'id' => 8,
                'pdf_template_id' => 1,
                'shortcode' => 'BOARD-SA',
                'title' => 'BOARD SELF-ASSESSMENT QUESTIONNAIRE',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('evaluation_form_sections')->insert([
            [
                'id' => 15,
                'evaluation_form_id' => 8,
                'rating_scale_id' => 3,
                'section_type_id' => 1,
                'title' => 'PERFORMANCE AS A GOVERNING BOARD',
                'add_remarks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'evaluation_form_id' => 8,
                'rating_scale_id' => 3,
                'section_type_id' => 1,
                'title' => 'COMPOSITION AND QUALITY OF LEADERSHIP',
                'add_remarks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 17,
                'evaluation_form_id' => 8,
                'rating_scale_id' => 3,
                'section_type_id' => 1,
                'title' => 'DECISION MAKING AND HANDLING OF ISSUES',
                'add_remarks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 18,
                'evaluation_form_id' => 8,
                'rating_scale_id' => 3,
                'section_type_id' => 1,
                'title' => 'SUPPORT STRUCTURE',
                'add_remarks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 19,
                'evaluation_form_id' => 8,
                'rating_scale_id' => 3,
                'section_type_id' => 1,
                'title' => 'PREVAILING CULTURE WITHIN THE BOARD',
                'add_remarks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 20,
                'evaluation_form_id' => 8,
                'rating_scale_id' => 3,
                'section_type_id' => 1,
                'title' => 'GUIDANCE AND FOLLOW-THROUGH',
                'add_remarks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 21,
                'evaluation_form_id' => 8,
                'rating_scale_id' => 3,
                'section_type_id' => 3,
                'title' => 'OTHER AREAS NOT MENTIONED ABOVE/SPECIAL CONCERNS THAT REQUIRES BOT ATTENTION/CONSIDERATION (Use additional sheet as necessary):',
                'add_remarks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Questionnaires
        Questionnaire::insert([
            // PERFORMANCE AS A GOVERNING BOARD (id = 15)
            ['section_id' => 15, 'name' => 'The Board Committee manifested accountability for the strategic decisions made and result of operations of the Association.'],
            ['section_id' => 15, 'name' => 'The Board has critiqued, questioned and approved Management’s proposal guided by sound governance principles.'],
            ['section_id' => 15, 'name' => 'The Board provided strategic guidance for Management succession and alignment of executive leadership with the company’s strategic challenges.'],
            ['section_id' => 15, 'name' => 'The Board promoted an aggressive, value-driven, and performance-oriented culture throughout the organization.'],
            ['section_id' => 15, 'name' => 'The Board maintained strategic involvement in major policy decisions and program execution that made a difference in the operations of the Association.'],

            // COMPOSITION AND QUALITY OF LEADERSHIP (id = 16)
            ['section_id' => 16, 'name' => 'The Board is imbued with a collectively a mix of appropriate skills, knowledge, and experience.'],
            ['section_id' => 16, 'name' => 'The members of the Board are self-starters and are independent-minded in dealing with company issues whether strategic or operational.'],
            ['section_id' => 16, 'name' => 'The Board is intolerant of mediocrity in both Management and Board effectiveness.'],
            ['section_id' => 16, 'name' => 'The Board carried out its duties and responsibilities pursuant to the highest standards of ethical conduct.'],
            ['section_id' => 16, 'name' => 'The Board acts as a collegial body and promoted the over-all interests of the AFPSLAI members, and not only specific sectors.'],

            // DECISION MAKING AND HANDLING OF ISSUES (id = 17)
            ['section_id' => 17, 'name' => 'The Board focused on activities that will generate value to the organization and the General Membership.'],
            ['section_id' => 17, 'name' => 'The Board observed consistency of issued handled and decisions it makes with the prevailing Vision, Mission, and Strategic Objectives as set.'],
            ['section_id' => 17, 'name' => 'The Board requested information that are reasonable in amount, expected response time, and relevant to the proper execution of their functions.'],

            // SUPPORT STRUCTURE (id = 18)
            ['section_id' => 18, 'name' => 'The Board is provided access to independent professional advice in the discharge of their duties.'],
            ['section_id' => 18, 'name' => 'The Board observed and respected the specific charters of each BOT Committee and set performance assessment based on these.'],
            ['section_id' => 18, 'name' => 'The Board observed and respected the authority, roles, responsibilities, accountabilities, and delineation of functions between itself, the CEO and Management.'],

            // PREVAILING CULTURE WITHIN THE BOARD (id = 19)
            ['section_id' => 19, 'name' => 'The Board promoted a culture characterized by meritocracy, open communication, and transparency in decision making.'],
            ['section_id' => 19, 'name' => 'The Board manifested observance of the principles of fairness, accountability, transparency, and ethical conduct in the discharge of its functions.'],
            ['section_id' => 19, 'name' => 'The Board and Management Team exhibited “constructive interaction” characterized by a healthy atmosphere of give and take.'],

            // GUIDANCE AND FOLLOW-THROUGH (id = 20)
            ['section_id' => 20, 'name' => 'The Board exerted diligent effort to follow through on recommendations raised during meetings, evaluation process, in pursuit of further improvement.'],
            ['section_id' => 20, 'name' => 'The Board carried out evaluation process leading to a clearer understanding of what it must to do to add value to the organization.'],
            ['section_id' => 20, 'name' => 'The Board conducted regular monitoring of the Association’s performance without breaching the authority lines set for management.'],
        ]);

    }
}
