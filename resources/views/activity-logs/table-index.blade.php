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
                        <p class="mt-1 text-sm text-slate-400">Entries are shown newest first and keep both descriptive context and field-level changes when available.</p>
                    </div>
                </div>

                <div class="panel-body">
                    @if ($activityLogs->count())
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="w-44">When</th>
                                        <th class="w-72">User</th>
                                        <th class="w-40">Module</th>
                                        <th class="w-40">Action</th>
                                        <th class="w-28">Record</th>
                                        <th>Summary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activityLogs as $activityLog)
                                        @php($changes = $activityLog->changeSummary())

                                        <tr>
                                            <td class="align-top">
                                                <div class="font-semibold text-slate-100">
                                                    {{ $activityLog->created_at?->format('M d, Y') ?? '--' }}
                                                </div>
                                                <div class="mt-1 text-sm text-slate-400">
                                                    {{ $activityLog->created_at?->format('h:i:s A') ?? '--' }}
                                                </div>
                                            </td>
                                            <td class="align-top">
                                                <div class="font-semibold text-slate-100">
                                                    {{ $activityLog->user?->name ?? 'System / Unknown User' }}
                                                </div>
                                                <div class="mt-1 text-sm text-slate-400 break-all">
                                                    {{ $activityLog->user?->email ?? '--' }}
                                                </div>
                                            </td>
                                            <td class="align-top">
                                                <span class="status-badge border-fuchsia-500/25 bg-fuchsia-500/10 text-fuchsia-200">
                                                    {{ strtoupper($activityLog->formattedModule()) }}
                                                </span>
                                            </td>
                                            <td class="align-top">
                                                <span class="status-badge border-emerald-500/25 bg-emerald-500/10 text-emerald-200">
                                                    {{ strtoupper($activityLog->formattedAction()) }}
                                                </span>
                                            </td>
                                            <td class="align-top">
                                                <span class="font-semibold text-slate-100">
                                                    {{ $activityLog->record_id ? '#' . $activityLog->record_id : '--' }}
                                                </span>
                                            </td>
                                            <td class="align-top">
                                                <div class="space-y-3">
                                                    <div class="font-semibold text-slate-100">
                                                        {{ $activityLog->description ?: 'Activity recorded without additional description.' }}
                                                    </div>

                                                    @if (! empty($changes))
                                                        <div class="space-y-1.5 text-sm text-slate-300">
                                                            @foreach ($changes as $change)
                                                                <div>
                                                                    <span class="font-medium text-slate-200">{{ $change['field'] }}:</span>
                                                                    <span class="text-slate-400">{{ $change['from'] }}</span>
                                                                    <span class="px-1 text-sky-300">-></span>
                                                                    <span class="text-slate-100">{{ $change['to'] }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif ($activityLog->new_values || $activityLog->old_values)
                                                        <div class="text-sm text-slate-400">
                                                            Structured audit data is attached, but no field-level differences were detected.
                                                        </div>
                                                    @endif

                                                    @if ($activityLog->ip_address)
                                                        <div class="text-xs text-slate-500">
                                                            IP: {{ $activityLog->ip_address }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
