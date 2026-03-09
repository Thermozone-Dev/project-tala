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
        Schema::create('other_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->unsignedBigInteger('trustee_evaluation_id')->nullable();
            $table->foreign(['trustee_evaluation_id'], 'table_other_comments_ibfk_1')->references(['id'])->on('trustee_has_evaluation')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['comment_id'], 'table_other_comments_ibfk_2')->references(['id'])->on('evaluation_form_sections')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_comments');
    }
};
