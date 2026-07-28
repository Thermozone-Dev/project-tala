<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            $table->foreignId('corporate_secretary_sign')->constrained('users');
            $table->foreignId('corporsecretariatate_secretary_sign')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            $table->dropForeign('corporate_secretary_sign');
            $table->dropForeign('corporsecretariatate_secretary_sign');
        });
    }
};
