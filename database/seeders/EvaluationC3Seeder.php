<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\EvaluationForm;
use App\Models\EvaluationFormSection;
use App\Models\Questionnaire;
use Illuminate\Database\Seeder;

class EvaluationC3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c3 = EvaluationForm::create([
            "pdf_template_id" => 2,
            "shortcode" => "C3",
            "title" => "BOT EVALUATION FORM C.3 - EVP – GM",
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $performanceSection = EvaluationFormSection::create([
            "evaluation_form_id" => $c3->id,
            "rating_scale_id" => 1,
            "section_type_id" => 1,
            "title" => "BOT Performance (to be rated by Members of the Governance Committee) - 70%",
            "add_remarks" => false,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        Questionnaire::insert([
            ["section_id" => $performanceSection->id, "name" => "Active and constructive participation in BOT/ Committee meetings (can disagree without being disagreeable)."],
            ["section_id" => $performanceSection->id, "name" => "Professionalism displayed during BOT/ Committee discussions."],
            ["section_id" => $performanceSection->id, "name" => "Competence displayed during BOT/ Committee discussions."],
            ["section_id" => $performanceSection->id, "name" => "Level of diligence manifested in preparing for BOT/ Committee meetings."],
            ["section_id" => $performanceSection->id, "name" => "Familiarity with company policies and operations."],
            ["section_id" => $performanceSection->id, "name" => "Observance of proper protocol during BOT / Committee meetings."],
            ["section_id" => $performanceSection->id, "name" => "Ability to justify the position Management is taking in relation to proposals elevated to BOT."],
            ["section_id" => $performanceSection->id, "name" => "Level of clarity of understanding on the role of the EVP-GM in the Management of the Association."],
        ]);

        $attendanceFormSection = EvaluationFormSection::create([
            "evaluation_form_id" => $c3->id,
            "rating_scale_id" => 2,
            "section_type_id" => 2,
            "title" => "Attendance in BOT / Committee Meetings & other related activities (to be rated by the Corporate Secretary) - 30 %",
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
            ["attendance_section_id" => $attendanceSection->id, "name" => "Board Meetings (Regular & Special)"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Governance Committee"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Audit & Compliance"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Risk Oversight"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Membership"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Credit & Collection"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Compensation"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Amendment"],
            ["attendance_section_id" => $attendanceSection->id, "name" => "Planning Sessions & other related activities"],
        ]);
    }
}
