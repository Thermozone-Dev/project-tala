<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceMeeting;
use App\Models\CalendarEventModel;
use Guava\Calendar\Filament\CalendarWidget as BaseCalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class CalendarWidget extends BaseCalendarWidget
{
    use HasPageShield;

    protected static ?int $sort = 7;

    public function getEvents(FetchInfo $info): Collection|Builder|array
    {
        $events = [];

        // Get real meetings from database
        $meetings = AttendanceMeeting::all()->take(3);

        foreach ($meetings as $meeting) {
            $meet_date = $meeting?->created_at ?? now();
            $events[] = new CalendarEventModel(
                'meeting-' . $meeting->id,
                $meeting->name,
                $meet_date,
                '#3b82f6',
                '#ffffff'
            );
        }

        // Add dummy evaluation events
        $dummyEvaluationDates = [
            Carbon::now()->addDays(2),
            Carbon::now()->addDays(7),
            Carbon::now()->addDays(14),
        ];

        foreach ($dummyEvaluationDates as $index => $date) {
            $events[] = new CalendarEventModel(
                'eval-' . $index,
                'Evaluation Period',
                $date,
                '#10b981',
                '#ffffff'
            );
        }

        // Add dummy board meeting events
        $dummyMeetingDates = [
            Carbon::now()->addDays(5),
            Carbon::now()->addDays(12),
            Carbon::now()->addDays(19),
        ];

        foreach ($dummyMeetingDates as $index => $date) {
            $events[] = new CalendarEventModel(
                'board-' . $index,
                'Board Meeting',
                $date,
                '#f59e0b',
                '#ffffff'
            );
        }

        // Add dummy committee meeting events
        $dummyCommitteeDates = [
            Carbon::now()->addDays(3),
            Carbon::now()->addDays(10),
            Carbon::now()->addDays(17),
        ];

        foreach ($dummyCommitteeDates as $index => $date) {
            $events[] = new CalendarEventModel(
                'committee-' . $index,
                'Committee Meeting',
                $date,
                '#8b5cf6',
                '#ffffff'
            );
        }

        return $events;
    }
}
