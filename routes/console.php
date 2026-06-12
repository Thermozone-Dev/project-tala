<?php

use App\Notifications\MeetingReminder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
            // Email notification
            $user->notify(new MeetingReminder($meeting, '30min'));

            // Bell icon notification
            Notification::make()
                ->title('Meeting in 30 Minutes')
                ->body("'{$meeting->title}' starts at " . $meeting->scheduled_at->format('h:i A'))
                ->icon('heroicon-o-clock')
                ->warning()
                ->actions([
                    Action::make('view')
                        ->label('View Meeting')
                        ->url($meeting->meeting_link)
                        ->button(),
                ])
                ->sendToDatabase($user);
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
            // Email notification
            $user->notify(new MeetingReminder($meeting, 'daily'));

            // Bell icon notification
            Notification::make()
                ->title('Meeting Tomorrow')
                ->body("'{$meeting->title}' is scheduled tomorrow at " . $meeting->scheduled_at->format('h:i A'))
                ->icon('heroicon-o-calendar')
                ->info()
                ->actions([
                    Action::make('view')
                        ->label('View Meeting')
                        ->url($meeting->meeting_link)
                        ->button(),
                ])
                ->sendToDatabase($user);
        }
    }
})->dailyAt('08:00');
