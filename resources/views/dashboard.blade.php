<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="mt-1 text-sm text-slate-400">
                Quick visibility into member status, finance alerts, and upcoming club updates.
            </p>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-7xl">
            @if (! empty($notifications))
                <div
                    x-data="{
                        notifications: @js($notifications),
                        currentIndex: 0,
                        get current() {
                            return this.notifications[this.currentIndex] ?? null;
                        },
                        startRotation() {
                            if (this.notifications.length < 2) {
                                return;
                            }

                            setInterval(() => {
                                this.currentIndex = (this.currentIndex + 1) % this.notifications.length;
                            }, 4500);
                        },
                    }"
                    x-init="startRotation()"
                    class="app-panel overflow-hidden px-4 py-3 sm:px-5"
                >
                    <div
                        class="flex items-center gap-3 rounded-2xl border px-4 py-3"
                        :class="{
                            'border-emerald-500/20 bg-emerald-500/10 text-emerald-100': current?.severity === 'success',
                            'border-amber-500/20 bg-amber-500/10 text-amber-100': current?.severity === 'warning',
                            'border-rose-500/20 bg-rose-500/10 text-rose-100': current?.severity === 'danger',
                        }"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border"
                            :class="{
                                'border-emerald-400/30 bg-emerald-400/10 text-emerald-200': current?.severity === 'success',
                                'border-amber-400/30 bg-amber-400/10 text-amber-200': current?.severity === 'warning',
                                'border-rose-400/30 bg-rose-400/10 text-rose-200': current?.severity === 'danger',
                            }"
                        >
                            <template x-if="current?.icon === 'currency'">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m4-14.5A3.5 3.5 0 0 0 12.5 3h-1A3.5 3.5 0 0 0 8 6.5c0 1.933 1.567 3.5 3.5 3.5h1A3.5 3.5 0 0 1 16 13.5 3.5 3.5 0 0 1 12.5 17h-1A3.5 3.5 0 0 1 8 13.5" />
                                </svg>
                            </template>
                            <template x-if="current?.icon === 'calendar'">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                                </svg>
                            </template>
                            <template x-if="current?.icon !== 'currency' && current?.icon !== 'calendar'">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v4h1m-6 4h10a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
                                </svg>
                            </template>
                        </div>

                        <p class="min-w-0 flex-1 truncate text-sm font-medium sm:text-[0.95rem]" x-text="current?.message"></p>
                    </div>
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                @if ($quickStats['can_view_finance'])
                    <div class="app-panel-muted p-5">
                        <p class="text-sm font-medium text-slate-400">Total Contributions</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-100">@money($quickStats['total_contributions'])</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.14em] text-emerald-300/70">Posted only</p>
                    </div>

                    <div class="app-panel-muted p-5">
                        <p class="text-sm font-medium text-slate-400">Total Expenses</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-100">@money($quickStats['total_expenses'])</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.14em] text-rose-300/70">Posted only</p>
                    </div>

                    <div class="app-panel-muted p-5">
                        <p class="text-sm font-medium text-slate-400">Net Balance</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-100">@money($quickStats['net_balance'])</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.14em] text-sky-300/70">Contributions less expenses</p>
                    </div>
                @endif

                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Total Members</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-100">{{ $quickStats['total_members'] }}</p>
                    <p class="mt-2 text-xs uppercase tracking-[0.14em] text-slate-400">Directory count</p>
                </div>

                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Active Members</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-100">{{ $quickStats['active_members'] }}</p>
                    <p class="mt-2 text-xs uppercase tracking-[0.14em] text-emerald-300/70">Currently active</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-sky-500/20 bg-gradient-to-br from-slate-900 via-slate-900 to-sky-950/60 shadow-[0_24px_80px_-36px_rgba(56,189,248,0.35)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-sky-400/10 via-sky-300/5 to-transparent"></div>

                <div class="panel-header relative">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-300/85">Club Updates</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-50">Announcements</h3>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300/80">
                            Published club updates appear here in full so everyone can read the complete announcement from the dashboard.
                        </p>
                    </div>
                </div>

                <div class="panel-body relative">
                    @if ($announcements->isNotEmpty())
                        <div class="space-y-4">
                            @foreach ($announcements as $announcement)
                                <article class="rounded-2xl border border-slate-700/70 bg-slate-950/55 p-5 shadow-[0_18px_45px_-30px_rgba(15,23,42,0.85)]">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h4 class="text-lg font-semibold text-slate-50">{{ $announcement->title }}</h4>
                                            @if ($announcement->event)
                                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-sky-200/90">
                                                    <span class="inline-flex items-center rounded-full border border-sky-500/20 bg-sky-500/10 px-3 py-1 uppercase tracking-[0.14em]">
                                                        {{ $announcement->kind === 'event' ? 'Event' : 'Announcement + Event' }}
                                                    </span>
                                                    <span>{{ $announcement->event->event_date?->format('M d, Y') ?? '--' }}</span>
                                                    @if ($announcement->event->start_time)
                                                        <span>
                                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $announcement->event->start_time)->format('h:i A') }}
                                                            @if ($announcement->event->end_time)
                                                                - {{ \Carbon\Carbon::createFromFormat('H:i:s', $announcement->event->end_time)->format('h:i A') }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                    @if ($announcement->event->location)
                                                        <span>{{ $announcement->event->location }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="mt-1 text-xs uppercase tracking-[0.16em] text-sky-300/80">
                                                    Published {{ $announcement->published_at?->format('M d, Y h:i A') ?? $announcement->created_at?->format('M d, Y h:i A') }}
                                                </div>
                                            @endif
                                        </div>

                                        @if ($announcement->creator)
                                            <span class="inline-flex items-center rounded-full border border-slate-700/80 bg-slate-900/80 px-3 py-1 text-xs text-slate-400">By {{ $announcement->creator->name }}</span>
                                        @endif
                                    </div>

                                    @if (filled($announcement->body))
                                        <div class="mt-4 whitespace-pre-line text-[0.95rem] leading-7 text-slate-200/90">
                                            {{ $announcement->body }}
                                        </div>
                                    @elseif ($announcement->event)
                                        <div class="mt-4 text-[0.95rem] leading-7 text-slate-300/90">
                                            {{ $announcement->event->description ?: 'This scheduled club event is now part of the shared dashboard feed.' }}
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-700/80 bg-slate-950/40 p-6">
                            <p class="text-sm text-slate-400">No published announcements are available right now.</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($monthlySnapshot)
                <div class="app-panel">
                    <div class="panel-header">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-100">Monthly Snapshot</h3>
                            <p class="mt-1 text-sm text-slate-400">Quick context for posted financial activity in {{ now()->format('F Y') }}.</p>
                        </div>
                    </div>

                    <div class="panel-body grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-5">
                            <p class="text-sm font-medium text-slate-400">This Month Contributions</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-100">@money($monthlySnapshot['contributions'])</p>
                        </div>

                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-5">
                            <p class="text-sm font-medium text-slate-400">This Month Expenses</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-100">@money($monthlySnapshot['expenses'])</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <div class="app-panel">
                    <div class="panel-header">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-100">Personal Section</h3>
                            <p class="mt-1 text-sm text-slate-400">A quick look at your own contribution and dues standing.</p>
                        </div>
                    </div>

                    <div class="panel-body grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-5">
                            <p class="text-sm font-medium text-slate-400">My Latest Contribution</p>
                            @if ($personalSection['latest_contribution'])
                                <p class="mt-2 text-2xl font-semibold text-slate-100">
                                    @money($personalSection['latest_contribution']->amount)
                                </p>
                                <p class="mt-2 text-sm text-slate-300">
                                    {{ $personalSection['latest_contribution']->category?->name ?? 'Contribution' }}
                                </p>
                                <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-400">
                                    {{ optional($personalSection['latest_contribution']->payment_date)->format('M d, Y') ?? '--' }}
                                </p>
                            @elseif ($personalSection['member'])
                                <p class="mt-2 text-sm text-slate-400">No posted contributions found for your member profile yet.</p>
                            @else
                                <p class="mt-2 text-sm text-slate-400">No linked member profile is available for this account.</p>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-5">
                            <p class="text-sm font-medium text-slate-400">My Dues Status</p>
                            @if ($personalSection['dues_status'])
                                <div class="mt-3">
                                    <span class="status-badge {{ $personalSection['dues_status']['state'] === 'paid' ? 'status-active' : 'status-inactive' }}">
                                        {{ $personalSection['dues_status']['label'] }}
                                    </span>
                                </div>
                                <p class="mt-3 text-sm text-slate-300">{{ $personalSection['dues_status']['detail'] }}</p>
                            @elseif ($personalSection['member'])
                                <p class="mt-2 text-sm text-slate-400">Monthly dues tracking is not available for your profile yet.</p>
                            @else
                                <p class="mt-2 text-sm text-slate-400">No linked member profile is available for dues tracking.</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($recentActivities->isNotEmpty())
                    <div class="app-panel">
                        <div class="panel-header">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-100">Recent Activity</h3>
                                <p class="mt-1 text-sm text-slate-400">Latest system actions shown in a light audit-friendly view.</p>
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="space-y-3">
                                @foreach ($recentActivities as $activity)
                                    <article class="rounded-2xl border border-slate-800/80 bg-slate-900/70 px-4 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-medium text-slate-100">{{ $activity->dashboardSummary() }}</p>
                                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                                    <span>{{ $activity->formattedModule() }}</span>
                                                    @if ($activity->record_id)
                                                        <span>• Record #{{ $activity->record_id }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="shrink-0 text-xs text-slate-500">{{ $activity->created_at?->format('M d, h:i A') ?? '--' }}</span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
