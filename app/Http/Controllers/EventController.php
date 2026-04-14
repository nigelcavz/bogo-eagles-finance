<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Models\ActivityLog;
use App\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $viewMode = $request->string('view', 'month')->toString();
        $viewMode = in_array($viewMode, ['month', 'week'], true) ? $viewMode : 'month';
        $selectedDate = $request->filled('date')
            ? Carbon::parse($request->string('date')->toString())
            : now();

        $monthStart = $selectedDate->copy()->startOfMonth()->startOfWeek();
        $monthEnd = $selectedDate->copy()->endOfMonth()->endOfWeek();
        $weekStart = $selectedDate->copy()->startOfWeek();
        $weekEnd = $selectedDate->copy()->endOfWeek();

        $events = Event::query()
            ->with('creator')
            ->whereBetween('event_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->orderBy('title')
            ->get();

        $eventsByDate = $events->groupBy(fn (Event $event) => $event->event_date->toDateString());

        $monthWeeks = collect(CarbonPeriod::create($monthStart, $monthEnd))
            ->map(function (Carbon $date) use ($selectedDate, $eventsByDate) {
                return [
                    'date' => $date->copy(),
                    'isCurrentMonth' => $date->month === $selectedDate->month,
                    'isToday' => $date->isToday(),
                    'events' => $eventsByDate->get($date->toDateString(), collect()),
                ];
            })
            ->chunk(7);

        $weekDays = collect(CarbonPeriod::create($weekStart, $weekEnd))
            ->map(function (Carbon $date) use ($eventsByDate) {
                return [
                    'date' => $date->copy(),
                    'isToday' => $date->isToday(),
                    'events' => $eventsByDate->get($date->toDateString(), collect()),
                ];
            });

        $monthEventDays = collect(CarbonPeriod::create($selectedDate->copy()->startOfMonth(), $selectedDate->copy()->endOfMonth()))
            ->map(function (Carbon $date) use ($eventsByDate) {
                return [
                    'date' => $date->copy(),
                    'isToday' => $date->isToday(),
                    'events' => $eventsByDate->get($date->toDateString(), collect()),
                ];
            })
            ->filter(fn (array $day) => $day['events']->isNotEmpty())
            ->values();

        return view('events.index', [
            'viewMode' => $viewMode,
            'selectedDate' => $selectedDate,
            'monthWeeks' => $monthWeeks,
            'weekDays' => $weekDays,
            'monthEventDays' => $monthEventDays,
            'canManageCalendar' => $request->user()?->canManageCalendar() ?? false,
            'monthPreviousDate' => $selectedDate->copy()->subMonth()->toDateString(),
            'monthNextDate' => $selectedDate->copy()->addMonth()->toDateString(),
            'weekPreviousDate' => $selectedDate->copy()->subWeek()->toDateString(),
            'weekNextDate' => $selectedDate->copy()->addWeek()->toDateString(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->canManageCalendar(), 403);

        return view('events.create', [
            'selectedDate' => $request->filled('date')
                ? Carbon::parse($request->string('date')->toString())->toDateString()
                : now()->toDateString(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageCalendar(), 403);

        $validated = $request->validated();

        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'event_created',
            'module' => 'events',
            'record_id' => $event->id,
            'description' => 'Calendar event created.',
            'old_values' => null,
            'new_values' => [
                'title' => $event->title,
                'event_date' => $event->event_date?->toDateString(),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'location' => $event->location,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()
            ->route('calendar.index', [
                'view' => 'month',
                'date' => $event->event_date?->toDateString(),
            ])
            ->with('success', 'Event scheduled successfully.');
    }
}
