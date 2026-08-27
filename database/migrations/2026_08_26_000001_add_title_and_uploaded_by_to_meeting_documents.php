<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_documents', function (Blueprint $table) {
            $table->string('title')->nullable()->after('original_filename');
            $table->unsignedBigInteger('uploaded_by')->nullable()->after('title');
            $table->foreign('uploaded_by', 'meeting_documents_uploaded_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_documents', function (Blueprint $table) {
            $table->dropForeign('meeting_documents_uploaded_by_fk');
            $table->dropColumn(['title', 'uploaded_by']);
        });
    }
};
