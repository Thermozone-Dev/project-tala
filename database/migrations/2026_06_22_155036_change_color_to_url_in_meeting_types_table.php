<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(Schema::hasColumn('meeting_types','color')){
            Schema::table('meeting_types', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }

        Schema::table('meeting_types', function (Blueprint $table) {

            $table->string('url')->nullable()->after('name');
        });

        DB::table('meeting_types')->delete();

        // Reset auto-increment/identity for both MySQL and MSSQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE meeting_types AUTO_INCREMENT = 1');
        } elseif (DB::getDriverName() === 'sqlsrv') {
            DB::statement('DBCC CHECKIDENT (meeting_types, RESEED, 0)');
        }

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
        
    }
};
