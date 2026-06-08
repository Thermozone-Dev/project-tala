<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\EvaluationForm;
use App\Models\EvaluationFormSection;
use App\Models\Questionnaire;
use Illuminate\Database\Seeder;

class EvaluationC2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c2 = EvaluationForm::create([
            "pdf_template_id" => 2,
            "shortcode" => "C2",
            "title" => "BOT EVALUATION FORM C.2 - TRUSTEES",
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $performanceSection = EvaluationFormSection::create([
            "evaluation_form_id" => $c2->id,
            "rating_scale_id" => 1,
            "section_type_id" => 1,
            "title" => "BOT Performance (to be rated by Members of the Governance Committee) - 70% ",
            "add_remarks" => false,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        Questionnaire::insert([
            ["section_id" => $performanceSection->id, "name" => "Active and constructive participation in BOT Committee meetings (can disagree without being disagreeable)"],
            ["section_id" => $performanceSection->id, "name" => "Professionalism displayed during BOT/Committee discussions"],
            ["section_id" => $performanceSection->id, "name" => "Competence displayed during BOT/Committee discussions"],
            ["section_id" => $performanceSection->id, "name" => "Level of diligence manifested in preparing for BOT/Committee meetings."],
            ["section_id" => $performanceSection->id, "name" => "Adoption of a critical yet constructive stance during deliberations of matters on hand."],
            ["section_id" => $performanceSection->id, "name" => "Observance of proper protocol during BOT/Committee meetings."],
            ["section_id" => $performanceSection->id, "name" => "Promotes the interest of AFPSLAI in all proposals advanced for BOT/Committee action."],
            ["section_id" => $performanceSection->id, "name" => "Promotes the interests of the sector being represented in all proposals advanced for BOT/ Management Action."],
            ["section_id" => $performanceSection->id, "name" => "Comes to meetings on time and finishes scheduled Board/ Committee meetings."],
            ["section_id" => $performanceSection->id, "name" => "Adopts a strategic mindset rather than a micro-management position."],
            ["section_id" => $performanceSection->id, "name" => "Respect given to fellow Trustees/ committee members (does not tend to monopolize discussion)."],
            ["section_id" => $performanceSection->id, "name" => "Level of clarity of understanding on the role of each Committee (does not infringe on other committee's functions)."],
        ]);

        $attendanceFormSection = EvaluationFormSection::create([
            "evaluation_form_id" => $c2->id,
            "rating_scale_id" => 2,
            "section_type_id" => 2,
            "title" => "Attendance in BOT Meetings (to be rated by the Corporate Secretary) - 30 %",
            "add_remarks" => false,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $attendanceSection = AttendanceSection::create([
            "section_id" => $attendanceFormSection->id,
            "show_total_meetings" => true,
            "show_physically_present" => true,
            "show_considered_present" => true,
            "show_total_present" => true,
            "show_attendance_rating" => true,
        ]);

        AttendanceMeeting::insert([
            ["attendance_section_id" => $attendanceSection->id, "name" => "BOT Meeting (Special & Regular)"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Governance Committee"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Audit & Compliance"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Risk Oversight"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Human Resource"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Credit& Collection"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Membership & Amendment"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "ITSC"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Planning Sessions & other related activities"],
        ]);
    }
}
