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
        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained('committees')->nullOnDelete();
            $table->foreignId('attendance_status_id')->nullable()->constrained('meeting_attendance_statuses')->nullOnDelete();
            $table->boolean('is_late')->default(false);
            $table->dateTime('mark_timestamp')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_attendees');
    }
};
