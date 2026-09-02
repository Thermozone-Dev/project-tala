<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_highlights', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->after('pdf_filename');
            $table->unsignedBigInteger('uploader_id')->nullable()->after('created_by');

            // MSSQL doesn't allow multiple cascade paths
            $isMssql = DB::getDriverName() === 'sqlsrv';

            $table->foreign('uploader_id', 'document_highlights_uploader_fk')
                ->references('id')
                ->on('users')
                ->onDelete($isMssql ? 'no action' : 'set null');
        });
    }

    public function down(): void
    {
        Schema::table('document_highlights', function (Blueprint $table) {
            $table->dropForeign('document_highlights_uploader_fk');
            $table->dropColumn(['original_filename', 'uploader_id']);
        });
    }
};
