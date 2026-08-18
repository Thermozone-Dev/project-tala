<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            $table->unsignedBigInteger('corporate_secretary_sign')->nullable();
            $table->unsignedBigInteger('secretariat')->nullable();

            $driver = DB::getDriverName();

            // MySQL supports multiple cascade paths; MSSQL does not
            if ($driver === 'mysql') {
                $table->foreign(['corporate_secretary_sign'], 'evaluation_period_ibfk_3')
                    ->references(['id'])->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign(['secretariat'], 'evaluation_period_ibfk_4')
                    ->references(['id'])->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
            } else {
                // MSSQL: use restrict to avoid multiple cascade paths
                $table->foreign(['corporate_secretary_sign'], 'evaluation_period_ibfk_3')
                    ->references(['id'])->on('users')
                    ->onUpdate('no action')->onDelete('no action');
                $table->foreign(['secretariat'], 'evaluation_period_ibfk_4')
                    ->references(['id'])->on('users')
                    ->onUpdate('no action')->onDelete('no action');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_period', function (Blueprint $table) {
            $table->dropForeign('evaluation_period_ibfk_3');
            $table->dropForeign('evaluation_period_ibfk_4');
            $table->dropColumn(['corporate_secretary_sign', 'secretariat',]);
        });
    }
};
