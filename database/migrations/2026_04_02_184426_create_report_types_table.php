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
        Schema::create('report_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('report_types')->insert([
            ['name' => 'BOT Performance Summary',                    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Individual Results of Rating - BOT',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Individual Results of Rating - CO & LRPs',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Summary Results of Committee Assessment',     'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_types');
    }
};
