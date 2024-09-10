<?php

namespace Spatie\CalendarTile;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Dashboard\Models\Tile;

class CalendarStore
{
    private Tile $tile;

    public static function make()
    {
        return new static();
    }

    public function __construct()
    {
        $this->tile = Tile::firstOrCreateForName('calendar');
    }

    public function setEventsForCalendarId(array $events, string $calendarId): self
    {
        $this->tile->putData('events_' . $calendarId, $events);

        return $this;
    }

    public function eventsForCalendarId(string $calendarId): Collection
    {
        return collect($this->tile->getData('events_' . $calendarId) ?? [])
            ->map(function (array $event) {
                $carbon = Carbon::createFromTimeString($event['date']);

                $event['date'] = $carbon;
                $event['withinWeek'] = $event['date']->diffInDays() < 7;

                $event['formattedDate'] = config('dashboard.tiles.calendar.presentable_dates', true)
                    ? $this->getPresentableDate($carbon)
                    : $this->getFormattedDate($event);

                return $event;
            });
    }

    public function eventsForCalendarIds(array $calendarIds): Collection
    {
        return collect($calendarIds)->flatMap(fn ($id) => $this->eventsForCalendarId($id))->sortBy('date');
    }

    public function getFormattedDate($event): string
    {
        $timeOptions = ['a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'v', '@', ':'];
        $dateTimeFormat = config('dashboard.tiles.calendar.date_format') ?? 'd.m.Y H:i';
        $dateFormat = trim(str_replace($timeOptions, '', $dateTimeFormat));

        $sortDate = Carbon::createFromTimeString($event['date']);
        $start = Carbon::createFromTimeString($event['start']);
        $end = Carbon::createFromTimeString($event['end']);

        if ($sortDate->isToday() && $event['allDay']) {
            return 'Today';
        }

        if ($start->diffInDays($end) > 1 && $event['allDay']) {
            return $start->format($dateFormat) . ' - ' . $end->format($dateFormat);
        }

        if ($event['allDay']) {
            return $sortDate->format($dateFormat);
        }

        if ($sortDate->diffInDays() < 7 && $event['start'] !== $event['end']) {
            $duration = $start->diffInMinutes($end) > 90 ? $start->diffInHours($end).'hr' : $start->diffInMinutes($end). 'min';
            return $sortDate->format($dateTimeFormat) . ' (' . $duration . ')' ;
        }

        return $sortDate->format($dateTimeFormat);
    }

    public function getPresentableDate(Carbon $carbon): string
    {
        if ($carbon->isToday()) {
            return 'Today';
        }

        if ($carbon->isTomorrow()) {
            return 'Tomorrow';
        }

        if ($carbon->diffInDays() < 8) {
            return "In {$carbon->diffInDays()} days";
        }

        if ($carbon->isNextWeek()) {
            return "Next week";
        }

        $dateFormat = config('dashboard.tiles.calendar.date_format') ?? 'd.m.Y';

        return $carbon->format($dateFormat);
    }
}
