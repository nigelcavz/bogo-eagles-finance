<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Calendar</h2>
                <p class="section-subheading">View club schedules in monthly or weekly format and keep upcoming events ready for future dashboard alerts.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($viewMode === 'month')
                    <a href="{{ route('calendar.index', ['view' => 'month', 'date' => $monthPreviousDate]) }}" class="btn-secondary">Previous</a>
                    <a href="{{ route('calendar.index', ['view' => 'month', 'date' => now()->toDateString()]) }}" class="btn-secondary">Today</a>
                    <a href="{{ route('calendar.index', ['view' => 'month', 'date' => $monthNextDate]) }}" class="btn-secondary">Next</a>
                @else
                    <a href="{{ route('calendar.index', ['view' => 'week', 'date' => $weekPreviousDate]) }}" class="btn-secondary">Previous</a>
                    <a href="{{ route('calendar.index', ['view' => 'week', 'date' => now()->toDateString()]) }}" class="btn-secondary">Today</a>
                    <a href="{{ route('calendar.index', ['view' => 'week', 'date' => $weekNextDate]) }}" class="btn-secondary">Next</a>
                @endif

                @if ($canManageCalendar)
                    <a href="{{ route('calendar.create', ['date' => $selectedDate->toDateString()]) }}" class="btn-primary">Add Event</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-7xl">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="app-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">
                            {{ $viewMode === 'month' ? $selectedDate->format('F Y') : 'Week of ' . $weekDays->first()['date']->format('M d, Y') }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-400">Switch between the monthly grid and weekly agenda view depending on how you want to review club events.</p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('calendar.index', ['view' => 'month', 'date' => $selectedDate->toDateString()]) }}" class="{{ $viewMode === 'month' ? 'btn-primary' : 'btn-secondary' }}">Monthly View</a>
                        <a href="{{ route('calendar.index', ['view' => 'week', 'date' => $selectedDate->toDateString()]) }}" class="{{ $viewMode === 'week' ? 'btn-primary' : 'btn-secondary' }}">Weekly View</a>
                    </div>
                </div>

                <div class="panel-body">
                    @if ($viewMode === 'month')
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[960px] table-fixed border-separate border-spacing-0 overflow-hidden rounded-2xl border border-slate-800/80">
                                <thead>
                                    <tr>
                                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                                            <th class="border-b border-r border-slate-800/80 bg-slate-900/90 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 last:border-r-0">
                                                {{ $dayName }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthWeeks as $week)
                                        <tr>
                                            @foreach ($week as $day)
                                                <td class="h-44 align-top border-b border-r border-slate-800/80 bg-slate-950/50 p-3 last:border-r-0 {{ $loop->parent->last ? 'last:border-b-0' : '' }} {{ $day['isCurrentMonth'] ? '' : 'opacity-55' }}">
                                                    <div class="mb-3 flex items-center justify-between">
                                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold {{ $day['isToday'] ? 'bg-sky-500/20 text-sky-200' : 'text-slate-200' }}">
                                                            {{ $day['date']->day }}
                                                        </span>
                                                        <span class="text-[11px] uppercase tracking-[0.16em] text-slate-500">{{ $day['date']->format('M') }}</span>
                                                    </div>

                                                    <div class="space-y-2">
                                                        @forelse ($day['events']->take(3) as $event)
                                                            <div class="rounded-xl border border-sky-500/15 bg-sky-500/10 px-3 py-2">
                                                                <p class="truncate text-sm font-medium text-slate-100">{{ $event->title }}</p>
                                                                <p class="mt-1 text-xs text-slate-300">
                                                                    {{ $event->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $event->start_time)->format('h:i A') : 'All day' }}
                                                                </p>
                                                            </div>
                                                        @empty
                                                            <p class="text-xs text-slate-500">No events</p>
                                                        @endforelse

                                                        @if ($day['events']->count() > 3)
                                                            <p class="text-xs font-medium text-sky-300">+{{ $day['events']->count() - 3 }} more</p>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($weekDays as $day)
                                <section class="rounded-2xl border border-slate-800/80 bg-slate-950/50 p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-base font-semibold text-slate-100">{{ $day['date']->format('l') }}</h4>
                                            <p class="mt-1 text-sm text-slate-400">{{ $day['date']->format('F d, Y') }}</p>
                                        </div>

                                        @if ($day['isToday'])
                                            <span class="status-badge border-sky-500/30 bg-sky-500/15 text-sky-200">Today</span>
                                        @endif
                                    </div>

                                    <div class="mt-4 space-y-3">
                                        @forelse ($day['events'] as $event)
                                            <article class="rounded-2xl border border-slate-800/80 bg-slate-900/80 px-4 py-4">
                                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                    <div>
                                                        <h5 class="text-sm font-semibold text-slate-100">{{ $event->title }}</h5>
                                                        @if ($event->description)
                                                            <p class="mt-2 text-sm leading-6 text-slate-300">{{ $event->description }}</p>
                                                        @endif
                                                    </div>

                                                    <div class="text-sm text-slate-400 md:text-right">
                                                        <div>
                                                            {{ $event->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $event->start_time)->format('h:i A') : 'All day' }}
                                                            @if ($event->end_time)
                                                                - {{ \Carbon\Carbon::createFromFormat('H:i:s', $event->end_time)->format('h:i A') }}
                                                            @endif
                                                        </div>
                                                        @if ($event->location)
                                                            <div class="mt-1">{{ $event->location }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @empty
                                            <p class="text-sm text-slate-500">No scheduled events for this day.</p>
                                        @endforelse
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
