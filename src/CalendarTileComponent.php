<?php

namespace Spatie\CalendarTile;

use Livewire\Component;

class CalendarTileComponent extends Component
{
    /** @var string */
    public $calendarId;

    /** @var array */
    public $calendarIds;

    /** @var string */
    public $position;

    /** @var string|null */
    public $title;

    /** @var int|null */
    public $refreshInSeconds;

    public function mount($calendarId = null, string $position, ?string $title = null, int $refreshInSeconds = null)
    {
        $this->calendarId = $calendarId;

        $this->position = $position;

        $this->title = $title;

        $this->refreshInSeconds = $refreshInSeconds;
    }

    public function render()
    {
        return view('dashboard-calendar-tile::tile', [
            'events' => is_array($this->calendarId)
                ? CalendarStore::make()->eventsForCalendarIds($this->calendarId)
                : CalendarStore::make()->eventsForCalendarId($this->calendarId),
            'refreshIntervalInSeconds' => $this->refreshInSeconds ?? config('dashboard.tiles.calendar.refresh_interval_in_seconds') ?? 60,
        ]);
    }
}
