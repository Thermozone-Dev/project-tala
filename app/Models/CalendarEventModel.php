<?php

namespace App\Models;

use Carbon\Carbon;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;

class CalendarEventModel implements Eventable
{
    protected string $id;
    protected string $title;
    protected Carbon $date;
    protected string $backgroundColor;
    protected string $textColor;

    public function __construct(
        string $id,
        string $title,
        Carbon $date,
        string $backgroundColor = '#3b82f6',
        string $textColor = '#ffffff'
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->date = $date;
        $this->backgroundColor = $backgroundColor;
        $this->textColor = $textColor;
    }

    public function getCalendarEventId(): string
    {
        return $this->id;
    }

    public function getCalendarEventTitle(): string
    {
        return $this->title;
    }

    public function getCalendarEventDescription(): ?string
    {
        return null;
    }

    public function getCalendarEventStart(): Carbon
    {
        return $this->date;
    }

    public function getCalendarEventEnd(): ?Carbon
    {
        return null;
    }

    public function getCalendarEventBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function getCalendarEventBorderColor(): ?string
    {
        return null;
    }

    public function getCalendarEventTextColor(): ?string
    {
        return $this->textColor;
    }

    public function getCalendarEventUrl(): ?string
    {
        return null;
    }

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make()
            ->title($this->title)
            ->start($this->date)
            ->end($this->date->copy()->addDay())
            ->backgroundColor($this->backgroundColor)
            ->textColor($this->textColor);
    }
}
