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
        Schema::table('meeting_types', function (Blueprint $table) {
            $table->dropColumn('color');
            $table->string('url')->nullable()->after('name');
        });

        DB::table('meeting_types')->delete();

        DB::statement('DBCC CHECKIDENT (meeting_types, RESEED, 0)');

        DB::table('meeting_types')->insert([
            [
                'name' => 'Google Meet',
                'url' => 'https://meet.google.com/new',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Microsoft Teams',
                'url' => 'https://teams.microsoft.com/1/meeting/new',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zoom',
                'url' => 'https://zoom.us/start/videomeeting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_types', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->string('color')->nullable();
        });

        DB::table('meeting_types')->truncate();

        DB::table('meeting_types')->insert([
            [
                'name' => 'Google Meet',
                'color' => '#00ac47',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Microsoft Teams',
                'color' => '#6264a7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zoom',
                'color' => '#2d8cff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
