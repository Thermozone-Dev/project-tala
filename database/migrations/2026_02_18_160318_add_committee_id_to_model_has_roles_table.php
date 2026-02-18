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
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('committee_id')->nullable()->after('model_id');

            $table->foreign('committee_id')
                ->references('id')
                ->on('committees')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign(['committee_id']);
            $table->dropColumn('committee_id');
        });
    }
};
