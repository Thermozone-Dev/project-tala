<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_documents', function (Blueprint $table) {
            $table->string('title')->nullable()->after('original_filename');
            $table->unsignedBigInteger('uploaded_by')->nullable()->after('title');

            // MSSQL compatible foreign key constraint
            $isMssql = DB::getDriverName() === 'sqlsrv';

            $table->foreign('uploaded_by', 'meeting_docs_uploaded_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete($isMssql ? 'no action' : 'set null');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_documents', function (Blueprint $table) {
            $table->dropForeign('meeting_docs_uploaded_by_fk');
            $table->dropColumn(['title', 'uploaded_by']);
        });
    }
};
