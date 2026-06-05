<?php

namespace Database\Seeders;

use App\Models\AttendanceMeeting;
use App\Models\AttendanceSection;
use App\Models\EvaluationForm;
use App\Models\EvaluationFormSection;
use App\Models\Questionnaire;
use Illuminate\Database\Seeder;

class EvaluationC4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c4 = EvaluationForm::create([
            "pdf_template_id" => 2,
            "shortcode" => "C4",
            "title" => "BOT EVALUATION FORM C.4 - Corporate Secretary",
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $performanceSection = EvaluationFormSection::create([
            "evaluation_form_id" => $c4->id,
            "rating_scale_id" => 1,
            "section_type_id" => 1,
            "title" => "Performance of Role as Corporate Officer - 70 %",
            "add_remarks" => false,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        Questionnaire::insert([
            ["section_id" => $performanceSection->id, "name" => "Accuracy of Board and Committee meeting minutes."],
            ["section_id" => $performanceSection->id, "name" => "Quality of agenda line up for the scheduled Board / Committee meeting."],
            ["section_id" => $performanceSection->id, "name" => "Timely distribution of Board and Committee folders."],
            ["section_id" => $performanceSection->id, "name" => "Mastery of the provisions of the Amended By-laws."],
            ["section_id" => $performanceSection->id, "name" => "Familiarity with past resolutions and matters of discussion by the Board."],
            ["section_id" => $performanceSection->id, "name" => "Quality of legal advice given to the Board as needed*"],
            ["section_id" => $performanceSection->id, "name" => "Familiarity with laws and regulations that govern the operations of AFPSLAI as an NSSLA."],
            ["section_id" => $performanceSection->id, "name" => "Efficiency in handling annual BOT assessments."],
            ["section_id" => $performanceSection->id, "name" => "Handling of proceedings during Annual Membership Meetings."],
            ["section_id" => $performanceSection->id, "name" => "Handling of records of Board and Committee meetings."],
            ["section_id" => $performanceSection->id, "name" => "Observance of proper protocol during Board/ Committee meetings."],
            ["section_id" => $performanceSection->id, "name" => "Level of clarity of understanding on the role of the Corporate Secretary."],
        ]);

        $attendanceFormSection = EvaluationFormSection::create([
            "evaluation_form_id" => $c4->id,
            "rating_scale_id" => 2,
            "section_type_id" => 2,
            "title" => "Attendance in BOT / Committee Meetings & other related activities (to be rated by the Head, OBS, Chairman\"s Office in lieu of the Corporate Secretary) - 30 %",
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
        ]);
    }
}
