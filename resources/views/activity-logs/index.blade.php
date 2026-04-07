<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Activity Tracker</h2>
                <p class="section-subheading">Review who changed what, when it happened, and how records moved from one value to another.</p>
            </div>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-7xl">
            <div class="app-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">Audit Activity</h3>
                        <p class="mt-1 text-sm text-slate-400">Entries are shown newest first and keep both descriptive context and detailed field-level changes when available.</p>
                    </div>
                </div>

                <div class="panel-body">
                    @if ($activityLogs->count())
                        <div class="space-y-4">
                            @foreach ($activityLogs as $activityLog)
                                @php($changes = $activityLog->changeSummary())

                                <article class="rounded-2xl border border-slate-800/80 bg-slate-950/40 p-5 shadow-sm">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="space-y-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="status-badge border-sky-500/30 bg-sky-500/15 text-sky-200">
                                                    {{ $activityLog->formattedModule() }}
                                                </span>
                                                <span class="status-badge border-slate-700/80 bg-slate-800/80 text-slate-200">
                                                    {{ $activityLog->formattedAction() }}
                                                </span>
                                                @if ($activityLog->record_id)
                                                    <span class="text-xs text-slate-500">Record #{{ $activityLog->record_id }}</span>
                                                @endif
                                            </div>

                                            <h4 class="text-base font-semibold text-slate-100">
                                                {{ $activityLog->description ?: 'Activity recorded without additional description.' }}
                                            </h4>

                                            <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-400">
                                                <span>
                                                    <span class="text-slate-500">By</span>
                                                    {{ $activityLog->user?->name ?? 'System / Unknown User' }}
                                                </span>
                                                <span>
                                                    <span class="text-slate-500">At</span>
                                                    {{ $activityLog->created_at?->format('M d, Y h:i A') ?? '--' }}
                                                </span>
                                                @if ($activityLog->ip_address)
                                                    <span>
                                                        <span class="text-slate-500">IP</span>
                                                        {{ $activityLog->ip_address }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if (! empty($changes))
                                        <div class="mt-5 rounded-2xl border border-slate-800/70 bg-slate-900/60 p-4">
                                            <h5 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-400">Detailed Changes</h5>

                                            <div class="mt-4 space-y-3">
                                                @foreach ($changes as $change)
                                                    <div class="grid gap-2 rounded-xl border border-slate-800/70 bg-slate-950/40 p-3 lg:grid-cols-[180px_1fr_auto_1fr] lg:items-center">
                                                        <div class="text-sm font-medium text-slate-200">{{ $change['field'] }}</div>
                                                        <div class="rounded-lg border border-slate-800 bg-slate-900/80 px-3 py-2 text-sm text-slate-400">
                                                            {{ $change['from'] }}
                                                        </div>
                                                        <div class="text-center text-sm font-semibold text-sky-300">→</div>
                                                        <div class="rounded-lg border border-sky-500/20 bg-sky-500/10 px-3 py-2 text-sm text-slate-100">
                                                            {{ $change['to'] }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif ($activityLog->new_values || $activityLog->old_values)
                                        <div class="mt-5 rounded-2xl border border-slate-800/70 bg-slate-900/60 p-4 text-sm text-slate-400">
                                            Structured audit data is attached to this activity, but no field-level differences were detected.
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $activityLogs->links() }}
                        </div>
                    @else
                        <p class="text-sm text-slate-400">No activity records are available yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
