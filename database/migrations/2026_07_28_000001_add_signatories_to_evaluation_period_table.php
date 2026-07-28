<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            $table->unsignedBigInteger('corporate_secretary_sign')
                ->nullable();

            $table->unsignedBigInteger('secretariat')
                ->nullable();

            $table->foreign('corporate_secretary_sign', 'evaluation_period_ibfk_3')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('secretariat', 'evaluation_period_ibfk_4')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            $table->dropForeign('evaluation_period_ibfk_3');
            $table->dropForeign('evaluation_period_ibfk_4');
            $table->dropColumn('corporate_secretary_sign');
            $table->dropColumn('secretariat');
        });
    }
};
