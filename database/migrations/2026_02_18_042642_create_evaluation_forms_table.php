<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------
        // PDF Templates
        // -------------------------
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);
            $table->softDeletes();
            $table->timestamps();
        });

        // -------------------------
        // Rating Scales
        // -------------------------
        Schema::create('rating_scales', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('rating_scale_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rating_scale_id')
                ->constrained('rating_scales')
                ->cascadeOnDelete();

            $table->string('name', 100)->nullable();
            $table->tinyInteger('value');
            $table->string('qualitative');
            $table->softDeletes();
            $table->timestamps();
        });

        // -------------------------
        // Section Types
        // -------------------------
        Schema::create('section_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);
            $table->softDeletes();
            $table->timestamps();
        });

        // -------------------------
        // Evaluation Forms
        // -------------------------
        Schema::create('evaluation_forms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pdf_template_id')
                ->constrained('pdf_templates')
                ->cascadeOnDelete();

            $table->string('shortcode', 15)->unique();
            $table->string('title');
            $table->softDeletes();
            $table->timestamps();
        });

        // -------------------------
        // Evaluation Form Sections
        // -------------------------
        Schema::create('evaluation_form_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluation_form_id')
                ->constrained('evaluation_forms')
                ->cascadeOnDelete();

            $table->foreignId('rating_scale_id')
                ->nullable()
                ->constrained('rating_scales')
                ->nullOnDelete();

            $table->foreignId('section_type_id')
                ->constrained('section_types')
                ->cascadeOnDelete();

            $table->string('title');
            $table->boolean('add_remarks')->default(false);

            $table->softDeletes();
            $table->timestamps();
        });

        // -------------------------
        // Attendance Sections
        // -------------------------
        Schema::create('attendance_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('section_id')
                ->constrained('evaluation_form_sections')
                ->cascadeOnDelete();

            $table->boolean('show_total_meetings')->default(true);
            $table->boolean('show_physically_present')->default(false);
            $table->boolean('show_considered_present')->default(false);
            $table->boolean('show_total_present')->default(false);
            $table->boolean('show_attendance_rating')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });

        // -------------------------
        // Attendance Meetings
        // -------------------------
        Schema::create('attendance_meetings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_section_id')
                ->constrained('attendance_sections')
                ->cascadeOnDelete();

            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        // -------------------------
        // Questionnaires
        // -------------------------
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();

            $table->foreignId('section_id')
                ->constrained('evaluation_form_sections')
                ->cascadeOnDelete();

            $table->mediumText('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
        Schema::dropIfExists('attendance_meetings');
        Schema::dropIfExists('attendance_sections');
        Schema::dropIfExists('evaluation_form_sections');
        Schema::dropIfExists('evaluation_forms');
        Schema::dropIfExists('rating_scale_values');
        Schema::dropIfExists('section_types');
        Schema::dropIfExists('rating_scales');
        Schema::dropIfExists('pdf_templates');
    }
};
