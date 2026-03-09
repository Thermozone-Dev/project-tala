<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_answer', function (Blueprint $table) {
            $table->id();
            $table->integer('total_meetings')->nullable();
            $table->integer('physically_present')->nullable();
            $table->integer('considered_present')->nullable();
            $table->integer('total_present')->nullable();
            $table->unsignedBigInteger('meeting_id')->nullable()->comment('Foreign key to meetings table');

            $table->unsignedBigInteger('attendance_rating_scale_values_id')->nullable()->comment('Foreign key to rating scale values for attendance rating');
            $table->unsignedBigInteger('trustee_evaluation_id')->nullable()->comment('Foreign key to trustee has evaluation');

            $table->foreign(['attendance_rating_scale_values_id'], 'table_attendance_answer_ibfk_1')->references(['id'])->on('rating_scale_values')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['trustee_evaluation_id'], 'table_attendance_answer_ibfk_2')->references(['id'])->on('trustee_has_evaluation')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['meeting_id'], 'table_attendance_answer_ibfk_3')->references(['id'])->on('attendance_meetings')->onUpdate('cascade')->onDelete('cascade');

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_answer');
    }
};
