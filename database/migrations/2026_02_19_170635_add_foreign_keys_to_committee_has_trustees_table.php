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
        Schema::table('committee_has_trustees', function (Blueprint $table) {
            $table->foreign(['committee_id'], 'committee_has_trustees_ibfk_1')->references(['id'])->on('committees')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['role_id'], 'committee_has_trustees_ibfk_2')->references(['id'])->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['user_id'], 'committee_has_trustees_ibfk_3')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committee_has_trustees', function (Blueprint $table) {
            $table->dropForeign('committee_has_trustees_ibfk_1');
            $table->dropForeign('committee_has_trustees_ibfk_2');
            $table->dropForeign('committee_has_trustees_ibfk_3');
        });
    }
};
