<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\CalendarEventModel;
use App\Models\Meeting;
use Guava\Calendar\Filament\CalendarWidget as BaseCalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CalendarWidget extends BaseCalendarWidget
{
    use HasPageShield;

    protected static ?int $sort = 7;

    protected bool $eventClickEnabled = true;

    public function getEvents(FetchInfo $info): Collection|Builder|array
    {
        $events = [];
        $user = Auth::user();

        $meetings = Meeting::query()
            ->with(['meetingType'])
            ->where('scheduled_at', '>=', today())
            ->when(
                !$user->hasRole(['Super Admin', 'Secretariat']),
                fn ($query) => $query->whereHas('attendees', function ($q) use ($user) {
                    $q->where('user_id', $user->id); // ← change to your FK column
                })
            )
            ->get();

        foreach ($meetings as $meeting) {

            $bgColor = $meeting->committee?->color ?? '#5b5d76';

            $textColor = $this->isReadableWithWhiteText($bgColor) ? '#ffffff' : '#111827';

            $events[] = new CalendarEventModel(
                'meeting-' . $meeting->id,
                $meeting->title,
                $meeting->scheduled_at,
                $bgColor,
                $textColor,
                Auth::user()->hasRole(['Super Admin', 'Secretariat'])
                    ? MeetingResource::getUrl('view', ['record' => $meeting->id])
                    : $meeting->meeting_link
            );
        }

        return $events;
    }

    function isReadableWithWhiteText($hexColor) {

        $hex = ltrim($hexColor, '#');

        // Convert hex to decimal RGB values
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Calculate YIQ brightness (human eye perception weight)
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        // Return true if the background is dark enough for white text
        return ($yiq < 128);
    }
}
