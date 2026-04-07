<x-app-layout>
    @php
        $activeFilterCount = collect([
            request('member_id'),
            request('contribution_category_id'),
            request('date_from'),
            request('date_to'),
            request('status') !== null && request('status') !== 'all' ? request('status') : null,
        ])->filter(fn ($value) => filled($value))->count();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Contributions Overview</h2>
                <p class="section-subheading">
                    Navigate by contribution type, monitor audit-friendly records, and open the monthly dues tracker.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('contribution-categories.index') }}"
                    class="btn-secondary"
                >
                    Manage Contribution Categories
                </a>

                <a
                    href="{{ route('contributions.create', ['back' => 'index']) }}"
                    class="btn-primary"
                >
                    Record Contribution
                </a>
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

            @if (session('error'))
                <div class="rounded-md bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-100 p-4 text-red-800">
                    <p class="font-semibold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ($typePages as $typePage)
                    <a href="{{ $typePage['route'] }}" class="app-panel-muted block p-5 transition duration-150 ease-in-out hover:border-slate-700 hover:bg-slate-900/90">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-100">{{ $typePage['category']->name }}</p>
                                <p class="mt-2 text-sm text-slate-400">
                                    {{ $typePage['category']->requiresMonthlyCoverage() ? 'Open the dedicated tracker view built from covered months.' : 'Open the contribution list for this specific payment type.' }}
                                </p>
                            </div>
                            <span class="status-badge {{ $typePage['category']->requiresMonthlyCoverage() ? 'border-sky-500/30 bg-sky-500/15 text-sky-200' : 'status-inactive' }}">
                                {{ $typePage['category']->requiresMonthlyCoverage() ? 'Tracker' : 'List' }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-800/80 pt-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Active Records</p>
                                <p class="mt-1 text-lg font-semibold text-slate-100">{{ $typePage['active_count'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Active Total</p>
                                <p class="mt-1 text-lg font-semibold text-sky-200">{{ number_format($typePage['active_total'], 2) }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Active Total</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-200">{{ number_format($activeTotal, 2) }}</p>
                </div>
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Voided Total</p>
                    <p class="mt-2 text-2xl font-semibold text-red-200">{{ number_format($voidedTotal, 2) }}</p>
                </div>
            </div>

            <div class="app-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">All Contribution Records</h3>
                        <p class="mt-1 text-sm text-slate-400">This overall list is useful for audit review across all contribution types.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($activeFilterCount > 0)
                            <span class="status-badge border-sky-500/30 bg-sky-500/15 text-sky-200">
                                {{ $activeFilterCount }} filter{{ $activeFilterCount === 1 ? '' : 's' }} active
                            </span>
                        @endif

                        <button
                            type="button"
                            x-data
                            x-on:click="$dispatch('open-modal', 'contribution-filters')"
                            class="btn-secondary inline-flex items-center gap-2"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M2.5 4.75A.75.75 0 013.25 4h13.5a.75.75 0 01.53 1.28L12 10.56V15a.75.75 0 01-.33.62l-3 2A.75.75 0 017.5 17v-6.44L2.22 5.28a.75.75 0 01.28-.53z" clip-rule="evenodd" />
                            </svg>
                            Filters
                        </button>
                    </div>
                </div>

                <div class="panel-body">
                    @if ($contributions->count())
                        <div class="overflow-x-auto">
                            <table class="data-table">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Member</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Coverage</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contributions as $contribution)
                                    <tr class="{{ $contribution->status === 'voided' ? 'bg-slate-900/40 text-slate-400 hover:bg-slate-900/55' : '' }}">
                                        <td>{{ $contribution->payment_date->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('members.show', $contribution->member) }}" class="font-medium {{ $contribution->status === 'voided' ? 'text-slate-300 hover:text-slate-100' : 'text-sky-200 hover:text-sky-100' }}">
                                                {{ $contribution->member->full_name }}
                                            </a>
                                        </td>
                                        <td>{{ $contribution->category->name }}</td>
                                        <td class="{{ $contribution->status === 'voided' ? 'text-slate-300' : 'font-semibold text-sky-200' }}">{{ number_format($contribution->amount, 2) }}</td>
                                        <td>
                                            {{ $contribution->coverages->sortBy(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->map(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->implode(', ') }}
                                        </td>
                                        <td>{{ $contribution->reference_number ?: '--' }}</td>
                                        <td>
                                            <div>
                                                <span class="status-badge {{ $contribution->status === 'voided' ? 'border-red-500/30 bg-red-500/15 text-red-200' : 'status-active' }}">
                                                    {{ ucfirst($contribution->status) }}
                                                </span>
                                            </div>
                                            @if ($contribution->status === 'voided')
                                                <p class="mt-2 text-xs text-red-200">
                                                    Voided by {{ $contribution->voider->name ?? 'Unknown user' }}:
                                                    {{ $contribution->void_reason }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="align-top">
                                            @if ($contribution->status === 'active')
                                                <div class="flex flex-wrap gap-2">
                                                    @if ($contribution->coverages->isEmpty() && ! $contribution->category->requiresMonthlyCoverage())
                                                        <a href="{{ route('contributions.edit', $contribution) }}" class="btn-secondary-accent">
                                                            Edit
                                                        </a>
                                                    @endif

                                                    <form
                                                        method="POST"
                                                        action="{{ route('contributions.void', $contribution) }}"
                                                        onsubmit="const reason = prompt('Reason for voiding this contribution:'); if (!reason) { return false; } this.querySelector('input[name=void_reason]').value = reason;"
                                                    >
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                                        <input type="hidden" name="void_reason">
                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center rounded-md border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-red-200 shadow-sm transition duration-150 ease-in-out hover:bg-red-500/20"
                                                        >
                                                            Void
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-500">Already voided</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $contributions->links() }}
                    </div>
                @else
                    <p class="text-sm text-slate-400">
                        No contributions match the selected filters yet.
                    </p>
                @endif
                </div>
            </div>
        </div>
    </div>

    <x-modal name="contribution-filters" :show="false" maxWidth="xl" focusable>
        <div class="border-b border-slate-800 px-6 py-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100">Search and Filters</h3>
                    <p class="mt-1 text-sm text-slate-400">Refine the overall contribution list without taking space away from the records table.</p>
                </div>

                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'contribution-filters')"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:border-slate-600 hover:text-slate-100"
                    aria-label="Close filters"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 11-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('contributions.index') }}" class="space-y-6 p-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="field-stack">
                    <label for="modal_member_id" class="block text-sm font-medium text-gray-700">Member</label>
                    <select id="modal_member_id" name="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All members</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected((string) request('member_id') === (string) $member->id)>
                                {{ $member->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-stack">
                    <label for="modal_contribution_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="modal_contribution_category_id" name="contribution_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('contribution_category_id') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-stack">
                    <label for="modal_date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input id="modal_date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div class="field-stack">
                    <label for="modal_date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input id="modal_date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div class="field-stack md:col-span-2">
                    <label for="modal_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="modal_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="all" @selected(request('status', 'all') === 'all')>All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="voided" @selected(request('status') === 'voided')>Voided</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-5">
                <x-primary-button>Apply Filters</x-primary-button>
                <a href="{{ route('contributions.index') }}" class="btn-secondary">Reset</a>
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'contribution-filters')"
                    class="btn-secondary"
                >
                    Close
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
