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
        Schema::create('meeting_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label');
            $table->string('color');
            $table->timestamps();
        });

        DB::table('meeting_statuses')->insert([
            ['name' => 'upcoming',  'label' => 'Upcoming',  'color' => 'warning', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ongoing',   'label' => 'Ongoing',   'color' => 'success', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'done',      'label' => 'Done',      'color' => 'gray',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cancelled', 'label' => 'Cancelled', 'color' => 'danger',  'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('meeting_url');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->foreignId('meeting_status_id')->default(1)->constrained('meeting_statuses')->noActionOnDelete();  // defaults = 'upcoming'
            $table->foreignId('created_by')->constrained('users')->noActionOnDelete();
            $table->timestamps();
        });

        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('attendance_status')->default('pending'); // pending, accepted, declined
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']); // prevent duplicate attendees
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_attendees');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('meeting_statuses');
    }
};
