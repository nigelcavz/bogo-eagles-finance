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
        <div class="page-content">
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

            <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
                <div class="app-panel">
                    <div class="panel-body">
                        <h3 class="text-lg font-semibold text-slate-100">Overview</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Use the dashboard as your quick entry point for contribution tracking, expense monitoring, member oversight, and audit-friendly activity across the club finance workflow.
                        </p>
                    </div>
                </div>

                <div class="app-panel-muted p-6">
                    <p class="text-sm font-medium uppercase tracking-[0.18em] text-sky-300/80">System Status</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-100">Online</p>
                    <p class="mt-2 text-sm leading-6 text-slate-400">
                        Notifications are role-aware and prioritize finance alerts before general club updates.
                    </p>
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
                                            <div class="mt-1 text-xs uppercase tracking-[0.16em] text-sky-300/80">
                                                Published {{ $announcement->published_at?->format('M d, Y h:i A') ?? $announcement->created_at?->format('M d, Y h:i A') }}
                                            </div>
                                        </div>

                                        @if ($announcement->creator)
                                            <span class="inline-flex items-center rounded-full border border-slate-700/80 bg-slate-900/80 px-3 py-1 text-xs text-slate-400">By {{ $announcement->creator->name }}</span>
                                        @endif
                                    </div>

                                    <div class="mt-4 whitespace-pre-line text-[0.95rem] leading-7 text-slate-200/90">
                                        {{ $announcement->body }}
                                    </div>
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
        </div>
    </div>
</x-app-layout>
