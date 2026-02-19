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
        Schema::create('committee_has_trustees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id')->index('committee_has_trustees_ibfk_2');
            $table->unsignedBigInteger('committee_id')->index('committee_has_trustees_ibfk_1');
            $table->unsignedBigInteger('user_id')->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_has_trustees');
    }
};
