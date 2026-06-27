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
        Schema::table('committees', function (Blueprint $table) {
            $table->string('color', 9)->nullable()->after('name');
        });

        $colors = [
            1 => '#970c10',
            2 => '#947360',
            3 => '#ff7f11',
            4 => '#50897d',
            5 => '#2596be',
            6 => '#a7abde',
            7 => '#ffa5d6',
        ];

        foreach ($colors as $id => $color) {
            DB::table('committees')
                ->where('id', $id)
                ->update(['color' => $color]);

        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
