<?php

use App\Notifications\MeetingReminder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Meeting;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 30min reminder
Schedule::call(function () {
    $meetings = Meeting::whereBetween('scheduled_at', [
            now()->addMinutes(29),
            now()->addMinutes(31),
        ])
        ->with('attendees.user')
        ->get();

    foreach ($meetings as $meeting) {
        $users = $meeting->attendees->pluck('user')->filter();

        foreach ($users as $user) {
            $user->notify(new MeetingReminder($meeting, '30min'));
        }
    }
})->everyMinute();

// Daily reminder
Schedule::call(function () {
    $meetings = Meeting::whereDate('scheduled_at', today()->addDay())
        ->with('attendees.user')
        ->get();

    foreach ($meetings as $meeting) {
        $users = $meeting->attendees->pluck('user')->filter();

        foreach ($users as $user) {
            $user->notify(new MeetingReminder($meeting, 'daily'));
        }
    }
})->dailyAt('08:00');
