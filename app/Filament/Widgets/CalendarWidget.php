<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\Meeting;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class  CalendarWidget extends FullCalendarWidget
{
    // protected string $view = 'filament.widgets.calendar-widget';

    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        // You can use $fetchInfo to filter events by date.
        // This method should return an array of event-like objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#returning-events
        // You can also return an array of EventData objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#the-eventdata-class
        return Meeting::query()
            ->ownedOrAssigned()
            ->where('scheduled_at', '>=', $fetchInfo['start'])
            ->where('scheduled_at', '<=', $fetchInfo['end'])
            ->get()
            ->map(fn (Meeting $meeting) => [
                'id'                => $meeting->id,
                'title'             => $meeting->title,
                'start'             => $meeting->start,
                'end'               => $meeting->end,
                'allDay'            => false,
                'backgroundColor' => '#019934',
                'borderColor' => '#648b2c',
                'display' => 'block',
                'url' => MeetingResource::getUrl(name: 'view', parameters: ['record' => $meeting->id]),
                'shouldOpenUrlInNewTab' => true,
            ])
            ->toArray();
    }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'right' => 'dayGridMonth,dayGridWeek,dayGridDay',
                'center' => 'title',
                'left' => 'prev,next today',
            ],
        ];
    }

    protected function headerActions(): array
    {
        return [];
    }
}
