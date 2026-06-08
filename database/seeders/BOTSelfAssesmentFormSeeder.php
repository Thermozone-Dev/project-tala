<?php

namespace Database\Seeders;

use App\Models\EvaluationForm;
use App\Models\EvaluationFormSection;
use App\Models\Questionnaire;
use Illuminate\Database\Seeder;

class BOTSelfAssesmentFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $botSA = EvaluationForm::create([
            "pdf_template_id" => 1,
            "shortcode" => "BOT-SA",
            "title" => "BOT COMMITTEE SELF-ASSESSMENT QUESTIONNAIRE",
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $performanceSection = EvaluationFormSection::create([
            "evaluation_form_id" => $botSA->id,
            "rating_scale_id" => 3,
            "section_type_id" => 1,
            "title" => "PERFORMANCE AS ADVISORY COMMITTEE OF THE BOT",
            "add_remarks" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $peopleSection = EvaluationFormSection::create([
            "evaluation_form_id" => $botSA->id,
            "rating_scale_id" => 3,
            "section_type_id" => 1,
            "title" => "PEOPLE ASSESSMENT",
            "add_remarks" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $issuesSection = EvaluationFormSection::create([
            "evaluation_form_id" => $botSA->id,
            "rating_scale_id" => 3,
            "section_type_id" => 1,
            "title" => "ISSUES ASSESSMENT",
            "add_remarks" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $processSection = EvaluationFormSection::create([
            "evaluation_form_id" => $botSA->id,
            "rating_scale_id" => 3,
            "section_type_id" => 1,
            "title" => "PROCESS ASSESSMENT",
            "add_remarks" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $cultureSection = EvaluationFormSection::create([
            "evaluation_form_id" => $botSA->id,
            "rating_scale_id" => 3,
            "section_type_id" => 1,
            "title" => "CULTURE ASSESSMENT",
            "add_remarks" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $followThroughSection = EvaluationFormSection::create([
            "evaluation_form_id" => $botSA->id,
            "rating_scale_id" => 3,
            "section_type_id" => 1,
            "title" => "FOLLOW-THROUGH ASSESSMENT",
            "add_remarks" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        EvaluationFormSection::create([
            "evaluation_form_id" => $botSA->id,
            "rating_scale_id" => 3,
            "section_type_id" => 3,
            "title" => "OTHER AREAS NOT MENTIONED ABOVE/SPECIAL CONCERNS THAT REQUIRES BOT ATTENTION/CONSIDERATION (Use additional sheet as necessary):",
            "add_remarks" => false,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        Questionnaire::insert([
            // PERFORMANCE AS ADVISORY COMMITTEE OF THE BOT
            ["section_id" => $performanceSection->id, "name" => "The Committee manifested accountability for the strategic decisions made by the Board based on its endorsement as an advisory Committee."],
            ["section_id" => $performanceSection->id, "name" => "The Committee has carried out its nominating, governance and performance evaluation efficiency within the period."],
            ["section_id" => $performanceSection->id, "name" => "The Committee has initiated improvements in the governance related policies and processes that created value to the Association."],

            // PEOPLE ASSESSMENT
            ["section_id" => $peopleSection->id, "name" => "The Committee carried out its duties and responsibilities pursuant to the highest standards of ethical conduct."],
            ["section_id" => $peopleSection->id, "name" => "The Committee has the right number of members with the right skills, experiences and expertise to be able to perform its mandate efficiently and effectively."],
            ["section_id" => $peopleSection->id, "name" => "The Committee has the right number of resource persons with the right skills, experiences and expertise that provides value added benefit to the Committee."],
            ["section_id" => $peopleSection->id, "name" => "The Committee manifested competence and diligence in financial performance assessments, strategic planning, succession management, and strategic policy setting governing company operations."],

            // ISSUES ASSESSMENT
            ["section_id" => $issuesSection->id, "name" => "The Committee focused on issues that will promote good governance principles across the organization."],
            ["section_id" => $issuesSection->id, "name" => "The Committee observed consistency of issues handled with its basic nominating, governance and performance evaluation functions per prevailing Charter."],
            ["section_id" => $issuesSection->id, "name" => "The Committee's request for information from management are used to identify improvement initiatives in governance related concerns and not merely for academic discussions only."],
            ["section_id" => $issuesSection->id, "name" => "The agenda items discussed by the Committee are strategic in nature."],

            // PROCESS ASSESSMENT
            ["section_id" => $processSection->id, "name" => "The Committee is provided proper information necessary for the proper and efficient discharge of its functions based on its approved charter."],
            ["section_id" => $processSection->id, "name" => "The agenda lined-up for Committee discussion has created value added benefits to the Association."],
            ["section_id" => $processSection->id, "name" => "The Committee endorsed governance related matters for BOT disposition only after a thorough deliberation that covers all possible concerns are carried out."],

            // CULTURE ASSESSMENT
            ["section_id" => $cultureSection->id, "name" => "The Committee promoted a culture characterized by meritocracy, open communication, and transparency in decision making."],
            ["section_id" => $cultureSection->id, "name" => "The Committee manifested observance of the principles of fairness, accountability, transparency, and ethical conduct in the discharge of its functions."],
            ["section_id" => $cultureSection->id, "name" => "The Committee promoted a culture that encourages free and open inter-action among attendees regardless of their role within the Committee structure."],

            // FOLLOW-THROUGH ASSESSMENT
            ["section_id" => $followThroughSection->id, "name" => "The Committee exerted diligent effort to follow through on recommendations raised during meetings, evaluation process, in pursuit of further improvement within its defined functions per its charter."],
            ["section_id" => $followThroughSection->id, "name" => "The Committee carried out evaluation process that leads to further enhancement of its role in the organization."],
            ["section_id" => $followThroughSection->id, "name" => "The Committee understands its role as an Advisory Committee to the BOT and makes follow-through assessments through the Pres & CEO as head of the Management Team."],
        ]);
    }
}
