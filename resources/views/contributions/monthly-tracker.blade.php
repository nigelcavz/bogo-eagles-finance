@php
    $canManage = auth()->user()?->canManageFinance() ?? false;
    $canViewMembers = auth()->user()?->canViewMembers() ?? false;
    $browseTypePages = collect($typePages ?? [])->reject(fn ($typePage) => $typePage['type'] === $type)->values();
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

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('contributions.index') }}" class="btn-secondary">
                    All Contributions
                </a>
                @if ($browseTypePages->isNotEmpty())
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button
                                type="button"
                                class="btn-secondary inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-2"
                            >
                                <span>Browse Types</span>
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @foreach ($browseTypePages as $typePage)
                                <x-dropdown-link :href="$typePage['route']">
                                    {{ $typePage['category']->name }}
                                </x-dropdown-link>
                            @endforeach
                        </x-slot>
                    </x-dropdown>
                @endif
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
                    @if ($errors->has('selections') || $errors->has('payment_date'))
                        <div class="mb-4 rounded-md border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">
                            @if ($errors->has('selections'))
                                <p>{{ $errors->first('selections') }}</p>
                            @endif
                            @if ($errors->has('payment_date'))
                                <p>{{ $errors->first('payment_date') }}</p>
                            @endif
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ $trackerSelectionAction }}"
                        x-data="{
                            selectedCells: @js(collect($trackerInitialSelections)
                                ->flatMap(function ($months, $memberId) {
                                    return collect($months)->map(fn ($month) => [
                                        'member_id' => (int) $memberId,
                                        'month' => (int) $month,
                                    ]);
                                })
                                ->values()
                                ->all()),
                            paymentDate: @js($trackerDefaultPaymentDate),
                            showConfirmation: false,
                            memberNames: @js($trackerRows->mapWithKeys(fn ($row) => [$row['member']->id => $row['member']->full_name])->all()),
                            monthLabels: @js($monthLabels),
                            toggleMonth(memberId, monthNumber) {
                                const existingIndex = this.selectedCells.findIndex((cell) => (
                                    cell.member_id === memberId && cell.month === monthNumber
                                ));

                                if (existingIndex >= 0) {
                                    this.selectedCells.splice(existingIndex, 1);
                                } else {
                                    this.selectedCells.push({
                                        member_id: memberId,
                                        month: monthNumber,
                                    });
                                }
                            },
                            isSelected(memberId, monthNumber) {
                                return this.selectedCells.some((cell) => (
                                    cell.member_id === memberId && cell.month === monthNumber
                                ));
                            },
                            clearSelection() {
                                this.selectedCells = [];
                                this.showConfirmation = false;
                            },
                            get entries() {
                                const grouped = this.selectedCells.reduce((carry, cell) => {
                                    const key = String(cell.member_id);

                                    if (!carry[key]) {
                                        carry[key] = [];
                                    }

                                    if (!carry[key].includes(cell.month)) {
                                        carry[key].push(cell.month);
                                    }

                                    return carry;
                                }, {});

                                return Object.entries(grouped)
                                    .map(([memberId, months]) => ({
                                        member_id: Number(memberId),
                                        months: [...months].sort((a, b) => a - b),
                                    }))
                                    .sort((left, right) => left.member_id - right.member_id);
                            },
                            get selectedMemberCount() {
                                return this.entries.length;
                            },
                            get selectedMonthCount() {
                                return this.selectedCells.length;
                            },
                            displayMonths(months) {
                                return months.map((month) => this.monthLabels[month] ?? month).join(', ');
                            },
                            openConfirmation() {
                                if (this.selectedMonthCount === 0) {
                                    return;
                                }

                                this.showConfirmation = true;
                            },
                        }"
                        class="space-y-4"
                    >
                        @csrf
                        <input type="hidden" name="coverage_year" value="{{ $year }}">

                        <template x-for="(entry, entryIndex) in entries" :key="entry.member_id">
                            <div>
                                <input type="hidden" :name="`selections[${entryIndex}][member_id]`" :value="entry.member_id">
                                <template x-for="(month, monthIndex) in entry.months" :key="`${entry.member_id}-${month}`">
                                    <input type="hidden" :name="`selections[${entryIndex}][months][${monthIndex}]`" :value="month">
                                </template>
                            </div>
                        </template>

                        <div class="relative max-h-[82vh] overflow-auto rounded-lg border border-slate-800/90">
                            <table class="w-full table-fixed text-sm text-slate-200">
                                <thead>
                                    <tr>
                                        <th class="sticky top-0 z-20 w-56 border-b border-slate-700/80 bg-slate-800 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-300 shadow-[0_1px_0_0_rgba(51,65,85,0.85)]">Member</th>
                                        @foreach ($monthLabels as $monthNumber => $monthLabel)
                                            <th class="sticky top-0 z-20 w-12 border-b border-slate-700/80 bg-slate-800 px-1.5 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-300 shadow-[0_1px_0_0_rgba(51,65,85,0.85)]">{{ $monthLabel }}</th>
                                        @endforeach
                                        <th class="sticky top-0 z-20 w-60 border-b border-slate-700/80 bg-slate-800 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-300 shadow-[0_1px_0_0_rgba(51,65,85,0.85)]">Summary</th>
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
                                            @foreach ($row['months'] as $monthNumber => $month)
                                                <td class="px-1 py-3 text-center align-middle">
                                                    @if ($month['duplicate'])
                                                        <span class="inline-flex min-w-[2.6rem] items-center justify-center whitespace-nowrap rounded-full border border-amber-500/30 bg-amber-500/15 px-2 py-1 text-[10px] font-semibold uppercase leading-none tracking-wide text-amber-200">
                                                            {{ $month['count'] }}x
                                                        </span>
                                                    @elseif ($month['covered'])
                                                        <span class="inline-flex min-w-[2.6rem] items-center justify-center whitespace-nowrap rounded-full border border-emerald-500/30 bg-emerald-500/15 px-2 py-1 text-[10px] font-semibold uppercase leading-none tracking-wide text-emerald-200">
                                                            OK
                                                        </span>
                                                    @elseif ($canManage)
                                                        <button
                                                            type="button"
                                                            @click="toggleMonth({{ $row['member']->id }}, {{ $monthNumber }})"
                                                            :class="isSelected({{ $row['member']->id }}, {{ $monthNumber }})
                                                                ? 'border-sky-400/40 bg-sky-500/20 text-sky-100 shadow-[0_0_0_1px_rgba(56,189,248,0.24)]'
                                                                : 'border-slate-700/80 bg-slate-800/70 text-slate-400 hover:border-slate-600 hover:bg-slate-700/80 hover:text-slate-200'"
                                                            class="inline-flex min-w-[2.6rem] items-center justify-center whitespace-nowrap rounded-full border px-2 py-1 text-[10px] font-semibold uppercase leading-none tracking-wide transition"
                                                        >
                                                            <span x-text="isSelected({{ $row['member']->id }}, {{ $monthNumber }}) ? 'New' : '--'"></span>
                                                        </button>
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
                                                        @if ($canViewMembers)
                                                            <a href="{{ route('members.show', $row['member']) }}" class="btn-secondary justify-center">
                                                                History
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="text-xs text-slate-400">
                            Tracker cells are derived from posted rows in <code>contribution_coverages</code>. Voided contribution payments are excluded from this yearly view.
                            @if ($canManage)
                                Click unpaid months to stage them, then confirm with the save bar below. Nothing is recorded until you press Save.
                            @endif
                        </p>

                        @if ($canManage)
                            <div
                                x-cloak
                                x-show="selectedMonthCount > 0"
                                class="sticky bottom-4 z-10 rounded-2xl border border-sky-500/20 bg-slate-950/95 p-4 shadow-[0_22px_60px_-30px_rgba(14,165,233,0.55)] backdrop-blur-sm"
                            >
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                    <div class="space-y-2">
                                        <p class="text-sm font-semibold text-slate-100">
                                            <span x-text="selectedMonthCount"></span>
                                            month<span x-show="selectedMonthCount !== 1">s</span>
                                            selected across
                                            <span x-text="selectedMemberCount"></span>
                                            member<span x-show="selectedMemberCount !== 1">s</span>
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            Review your selections, set the payment date, and save to create the monthly dues records.
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                        <div class="field-stack">
                                            <label for="tracker-payment-date" class="block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Payment Date</label>
                                            <input
                                                id="tracker-payment-date"
                                                name="payment_date"
                                                type="date"
                                                x-model="paymentDate"
                                                class="block w-full rounded-xl border border-slate-800 bg-slate-900/80 px-3 py-2 text-sm text-slate-100 shadow-sm focus:border-sky-500/60 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                                            >
                                        </div>

                                        <button type="button" @click="clearSelection()" class="btn-secondary justify-center">
                                            Clear
                                        </button>

                                        <button type="button" @click="openConfirmation()" class="btn-primary justify-center">
                                            Save Selected Months
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div
                                x-cloak
                                x-show="showConfirmation"
                                x-transition.opacity
                                x-on:keydown.escape.window="showConfirmation = false"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
                            >
                                <div
                                    x-show="showConfirmation"
                                    x-transition
                                    class="w-full max-w-3xl rounded-3xl border border-sky-500/20 bg-slate-950 shadow-[0_28px_90px_-36px_rgba(14,165,233,0.45)]"
                                    @click.outside="showConfirmation = false"
                                >
                                    <div class="border-b border-slate-800/80 px-6 py-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-300/85">Confirm Batch Save</p>
                                                <h3 class="mt-2 text-lg font-semibold text-slate-100">Review Monthly Dues Selections</h3>
                                                <p class="mt-2 text-sm text-slate-400">
                                                    Please confirm who will be recorded for this payment batch before saving it to the ledger.
                                                </p>
                                            </div>

                                            <button
                                                type="button"
                                                @click="showConfirmation = false"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-800 bg-slate-900/80 text-slate-400 transition hover:border-slate-700 hover:text-slate-200"
                                            >
                                                <span class="sr-only">Close confirmation</span>
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-5 px-6 py-5">
                                        <div class="grid gap-4 md:grid-cols-[auto_1fr] md:items-start">
                                            <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 px-4 py-3">
                                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Payment Date</p>
                                                <p class="mt-1 text-sm font-medium text-slate-100" x-text="paymentDate || '--'"></p>
                                            </div>
                                            <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 px-4 py-3">
                                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Batch Summary</p>
                                                <p class="mt-1 text-sm text-slate-200">
                                                    <span x-text="selectedMonthCount"></span>
                                                    month<span x-show="selectedMonthCount !== 1">s</span>
                                                    across
                                                    <span x-text="selectedMemberCount"></span>
                                                    member<span x-show="selectedMemberCount !== 1">s</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/60">
                                            <div class="border-b border-slate-800/80 px-4 py-3">
                                                <p class="text-sm font-semibold text-slate-100">Members Included in This Save</p>
                                            </div>

                                            <div class="max-h-[24rem] overflow-y-auto px-4 py-3">
                                                <div class="space-y-3">
                                                    <template x-for="entry in entries" :key="`confirm-${entry.member_id}`">
                                                        <div class="rounded-2xl border border-slate-800/70 bg-slate-950/70 px-4 py-3">
                                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                                <div>
                                                                    <p class="text-sm font-semibold text-slate-100" x-text="memberNames[entry.member_id] ?? `Member #${entry.member_id}`"></p>
                                                                    <p class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-400">Covered Months</p>
                                                                </div>
                                                                <p class="text-sm text-sky-200" x-text="displayMonths(entry.months)"></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 border-t border-slate-800/80 px-6 py-5 sm:flex-row sm:justify-end">
                                        <button type="button" @click="showConfirmation = false" class="btn-secondary justify-center">
                                            Back
                                        </button>
                                        <button type="submit" class="btn-primary justify-center">
                                            Confirm and Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
