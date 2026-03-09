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
        Schema::create('questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('questionnaire_id');
            $table->unsignedBigInteger('rating_scale_values_id')->nullable()->comment('Foreign key to rating scale values');
            $table->unsignedBigInteger('trustee_evaluation_id')->nullable()->comment('Foreign key to trustee has evaluation');
            $table->mediumText('remarks')->nullable();
            $table->foreign(['questionnaire_id'], 'table_questionaire_answers_ibfk_1')->references(['id'])->on('questionnaires')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['rating_scale_values_id'], 'table_questionaire_answers_ibfk_2')->references(['id'])->on('rating_scale_values')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['trustee_evaluation_id'], 'table_questionaire_answers_ibfk_3')->references(['id'])->on('trustee_has_evaluation')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['trustee_evaluation_id','questionnaire_id'], 'unique_answer_per_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_answers');
    }
};
