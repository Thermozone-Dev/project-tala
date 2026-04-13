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
         Schema::table('attendance_answer', function (Blueprint $table) {
            $table->dropForeign('table_attendance_answer_ibfk_2');
            $table->dropForeign('table_attendance_answer_ibfk_3');
            $table->unsignedBigInteger('evaluation_period_id')->nullable();
            $table->unsignedBigInteger('committee_id')->nullable();
            $table->unsignedBigInteger('trustee_id')->nullable();
            $table->dropColumn('meeting_id');
            $table->dropColumn('trustee_evaluation_id');

            $table->foreign(['evaluation_period_id'], 'table_attendance_answer_ibfk_2')->references(['id'])->on('evaluation_period')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['committee_id'], 'table_attendance_answer_ibfk_3')->references(['id'])->on('committees')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['trustee_id'], 'table_attendance_answer_ibfk_4')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_answer', function (Blueprint $table) {
            $table->dropForeign('table_attendance_answer_ibfk_2');
            $table->dropForeign('table_attendance_answer_ibfk_3');
            $table->dropForeign('table_attendance_answer_ibfk_4');

            $table->dropColumn('trustee_id');
            $table->dropColumn('committee_id');
            $table->dropColumn('evaluation_period_id');

            $table->unsignedBigInteger('meeting_id')->nullable()->comment('Foreign key to meetings table');
            $table->unsignedBigInteger('trustee_evaluation_id')->nullable()->comment('Foreign key to trustee has evaluation');

            $table->foreign(['trustee_evaluation_id'], 'table_attendance_answer_ibfk_2')->references(['id'])->on('trustee_has_evaluation')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['meeting_id'], 'table_attendance_answer_ibfk_3')->references(['id'])->on('attendance_meetings');

        });

    }
};
