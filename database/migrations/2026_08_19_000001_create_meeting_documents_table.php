<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0); // Track file size
            $table->timestamps();

            // MSSQL doesn't allow multiple cascade paths
            $isMssql = DB::getDriverName() === 'sqlsrv';

            $table->foreign('meeting_id', 'meeting_documents_meeting_id_fk')
                ->references('id')
                ->on('meetings')
                ->onDelete($isMssql ? 'no action' : 'cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_documents');
    }
};
