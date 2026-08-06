<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            $table->unsignedBigInteger('corporate_secretary_sign');
            $table->unsignedBigInteger('secretariat');
            $table->foreign(['corporate_secretary_sign'], 'evaluation_period_ibfk_3')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['secretariat'], 'evaluation_period_ibfk_4')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            // $table->dropForeign('evaluation_period_corporate_secretary_sign_foreign');
            // $table->dropForeign('evaluation_period_corporatesecretariatate_secretary_sign_foreign');

            $table->dropForeign(['corporate_secretary_sign']);
            $table->dropForeign(['corporsecretariatate_secretary_sign']);



            // $table->dropForeign('corporsecretariatate_secretary_sign');
            // $table->dropForeign('evaluation_period_ibfk_4');
            $table->dropColumn(['corporate_secretary_sign', 'corporsecretariatate_secretary_sign',]);
        });
    }
};
