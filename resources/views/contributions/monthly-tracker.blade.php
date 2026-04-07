@php
    $canManage = in_array(auth()->user()?->role, ['admin', 'treasurer'], true);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $category->name }} Tracker
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    One row per member, using actual posted coverage rows to show which months are already paid for {{ $year }}.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contributions.index') }}" class="btn-secondary">
                    All Contributions
                </a>
                @if ($canManage)
                    <a href="{{ route('contributions.create', ['contribution_category_id' => $category->id, 'back' => 'type', 'type' => $type, 'year' => $year]) }}" class="btn-primary">
                        Record Monthly Dues
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-[96rem]">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($duplicateMemberCount > 0)
                <div class="rounded-md bg-yellow-100 p-4 text-yellow-900">
                    {{ $duplicateMemberCount }} member {{ \Illuminate\Support\Str::plural('row', $duplicateMemberCount) }} currently show duplicate posted month coverage in {{ $year }}. This usually means older records were saved before duplicate protection was enforced.
                </div>
            @endif

            <div class="grid gap-4 xl:grid-cols-4">
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Fully Paid</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-300">{{ $fullyPaidCount }}</p>
                </div>
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Partially Paid</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-300">{{ $partialCount }}</p>
                </div>
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Unpaid</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-200">{{ $unpaidCount }}</p>
                </div>
                <div class="app-panel p-5">
                    <form method="GET" action="{{ route('contributions.types.show', ['type' => $type]) }}" class="field-stack">
                        <label for="year" class="block text-sm font-medium text-gray-700">Tracker Year</label>
                        <div class="flex gap-3">
                            <select id="year" name="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach ($availableYears as $availableYear)
                                    <option value="{{ $availableYear }}" @selected((int) $availableYear === (int) $year)>{{ $availableYear }}</option>
                                @endforeach
                            </select>
                            <x-primary-button>View</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="app-panel">
                <div class="panel-body">
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed overflow-hidden rounded-lg border border-slate-800/90 text-sm text-slate-200">
                            <thead>
                                <tr>
                                    <th class="w-56 border-b border-slate-700/80 bg-slate-800 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Member</th>
                                    @foreach ($monthLabels as $monthLabel)
                                        <th class="w-12 border-b border-slate-700/80 bg-slate-800 px-1.5 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-300">{{ $monthLabel }}</th>
                                    @endforeach
                                    <th class="w-60 border-b border-slate-700/80 bg-slate-800 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Summary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trackerRows as $row)
                                    <tr class="border-t border-slate-800/80 bg-slate-900/80 transition-colors duration-150 even:bg-slate-900 hover:bg-slate-800/80">
                                        <td class="px-4 py-3 align-middle">
                                            <div class="font-medium text-slate-100">{{ $row['member']->full_name }}</div>
                                            @if ($row['member']->member_code)
                                                <div class="text-xs text-slate-400">{{ $row['member']->member_code }}</div>
                                            @endif
                                        </td>
                                        @foreach ($row['months'] as $month)
                                            <td class="px-1 py-3 text-center align-middle">
                                                @if ($month['duplicate'])
                                                    <span class="inline-flex min-w-[2.6rem] items-center justify-center whitespace-nowrap rounded-full border border-amber-500/30 bg-amber-500/15 px-2 py-1 text-[10px] font-semibold uppercase leading-none tracking-wide text-amber-200">
                                                        {{ $month['count'] }}x
                                                    </span>
                                                @elseif ($month['covered'])
                                                    <span class="inline-flex min-w-[2.6rem] items-center justify-center whitespace-nowrap rounded-full border border-emerald-500/30 bg-emerald-500/15 px-2 py-1 text-[10px] font-semibold uppercase leading-none tracking-wide text-emerald-200">
                                                        OK
                                                    </span>
                                                @else
                                                    <span class="inline-flex min-w-[2.6rem] items-center justify-center whitespace-nowrap rounded-full border border-slate-700/80 bg-slate-800/70 px-2 py-1 text-[10px] font-semibold uppercase leading-none tracking-wide text-slate-400">
                                                        --
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 align-middle">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-400">
                                                        <span>Total Paid</span>
                                                        <span class="text-right font-semibold text-slate-100">@money($row['total_paid'])</span>
                                                        <span>Remaining</span>
                                                        <span class="text-right text-slate-200">{{ $row['unpaid_month_count'] }} months</span>
                                                    </div>

                                                    <div class="mt-3">
                                                        @if ($row['status'] === 'fully_paid')
                                                            <span class="status-badge status-active">Fully Paid</span>
                                                        @elseif ($row['status'] === 'partial')
                                                            <span class="status-badge border-amber-500/30 bg-amber-500/15 text-amber-200">Partial</span>
                                                        @else
                                                            <span class="status-badge status-inactive">Unpaid</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex shrink-0 flex-col gap-2">
                                                    @if ($canManage)
                                                        <a href="{{ route('contributions.create', ['member_id' => $row['member']->id, 'contribution_category_id' => $category->id, 'back' => 'type', 'type' => $type, 'year' => $year]) }}" class="btn-secondary-accent justify-center">
                                                            Record
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('members.show', $row['member']) }}" class="btn-secondary justify-center">
                                                        History
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        Tracker cells are derived from posted rows in <code>contribution_coverages</code>. Voided contribution payments are excluded from this yearly view.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
