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
                    One row per member, using actual active coverage rows to show which months are already paid for {{ $year }}.
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
                    {{ $duplicateMemberCount }} member {{ \Illuminate\Support\Str::plural('row', $duplicateMemberCount) }} currently show duplicate active month coverage in {{ $year }}. This usually means older records were saved before duplicate protection was enforced.
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
                        <table class="data-table min-w-[1400px]">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    @foreach ($monthLabels as $monthLabel)
                                        <th>{{ $monthLabel }}</th>
                                    @endforeach
                                    <th>Total Paid</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trackerRows as $row)
                                    <tr>
                                        <td>
                                            <div class="font-medium text-slate-100">{{ $row['member']->full_name }}</div>
                                            @if ($row['member']->member_code)
                                                <div class="text-xs text-slate-400">{{ $row['member']->member_code }}</div>
                                            @endif
                                        </td>
                                        @foreach ($row['months'] as $month)
                                            <td class="text-center">
                                                @if ($month['duplicate'])
                                                    <span class="inline-flex min-w-[3.25rem] items-center justify-center whitespace-nowrap rounded-full border border-amber-500/30 bg-amber-500/15 px-2.5 py-1 text-[11px] font-semibold uppercase leading-none tracking-wide text-amber-200">
                                                        {{ $month['count'] }}x
                                                    </span>
                                                @elseif ($month['covered'])
                                                    <span class="inline-flex min-w-[3.25rem] items-center justify-center whitespace-nowrap rounded-full border border-emerald-500/30 bg-emerald-500/15 px-2.5 py-1 text-[11px] font-semibold uppercase leading-none tracking-wide text-emerald-200">
                                                        Paid
                                                    </span>
                                                @else
                                                    <span class="inline-flex min-w-[3.25rem] items-center justify-center whitespace-nowrap rounded-full border border-slate-700/80 bg-slate-800/70 px-2.5 py-1 text-[11px] font-semibold uppercase leading-none tracking-wide text-slate-400">
                                                        --
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="font-semibold text-slate-100">{{ number_format($row['total_paid'], 2) }}</td>
                                        <td>
                                            <span class="text-sm text-slate-200">{{ $row['unpaid_month_count'] }} months</span>
                                        </td>
                                        <td>
                                            @if ($row['status'] === 'fully_paid')
                                                <span class="status-badge status-active">Fully Paid</span>
                                            @elseif ($row['status'] === 'partial')
                                                <span class="status-badge border-amber-500/30 bg-amber-500/15 text-amber-200">Partial</span>
                                            @else
                                                <span class="status-badge status-inactive">Unpaid</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-2">
                                                @if ($canManage)
                                                    <a href="{{ route('contributions.create', ['member_id' => $row['member']->id, 'contribution_category_id' => $category->id, 'back' => 'type', 'type' => $type, 'year' => $year]) }}" class="btn-secondary-accent">
                                                        Record
                                                    </a>
                                                @endif
                                                <a href="{{ route('members.show', $row['member']) }}" class="btn-secondary">
                                                    History
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        Tracker cells are derived from active rows in <code>contribution_coverages</code>. Voided contribution payments are excluded from this yearly view.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
