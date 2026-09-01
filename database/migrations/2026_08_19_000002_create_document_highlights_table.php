<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_highlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_document_id');
            $table->text('highlighted_text');
            $table->integer('start_offset');
            $table->integer('end_offset');
            $table->string('pdf_filename')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // MSSQL doesn't allow multiple cascade paths, use conditional logic
            $isMssql = DB::getDriverName() === 'sqlsrv';

            $table->foreign('meeting_document_id', 'document_highlights_meeting_document_fk')
                ->references('id')
                ->on('meeting_documents')
                ->onDelete($isMssql ? 'no action' : 'cascade');

            $table->foreign('created_by', 'document_highlights_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete($isMssql ? 'no action' : 'cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_highlights');
    }
};
