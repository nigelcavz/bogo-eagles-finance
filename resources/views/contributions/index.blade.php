<x-app-layout>
    @php
        $canManageFinance = auth()->user()?->canManageFinance() ?? false;
        $canViewMembers = auth()->user()?->canViewMembers() ?? false;
        $activeFilterCount = collect([
            request('member_id'),
            request('contribution_category_id'),
            request('date_from'),
            request('date_to'),
            request('status') !== null && request('status') !== 'all' ? request('status') : null,
        ])->filter(fn ($value) => filled($value))->count();
        $featuredTypePage = collect($typePages)->first(fn ($typePage) => $typePage['category']->requiresMonthlyCoverage());
        $browseTypePages = collect($typePages)->reject(fn ($typePage) => $typePage['category']->requiresMonthlyCoverage())->values();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h2 class="section-heading">Contributions Overview</h2>
                <p class="section-subheading max-w-3xl">
                    Navigate by contribution type, monitor audit-friendly records, and open the monthly dues tracker.
                </p>
            </div>

            <div class="relative z-40 flex shrink-0 flex-wrap items-center gap-2 lg:flex-nowrap lg:justify-end">
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

                @if ($canManageFinance)
                    <a
                        href="{{ route('contribution-categories.index') }}"
                        class="btn-secondary whitespace-nowrap px-3 py-2"
                    >
                        Manage Contribution Categories
                    </a>

                    <a
                        href="{{ route('contributions.create', ['back' => 'index']) }}"
                        class="btn-primary whitespace-nowrap px-3.5 py-2"
                    >
                        Record Contribution
                    </a>
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

            @if ($featuredTypePage)
                <a href="{{ $featuredTypePage['route'] }}" class="app-panel-muted block p-6 transition duration-150 ease-in-out hover:border-slate-700 hover:bg-slate-900/90">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <div class="flex items-center gap-3">
                                <h3 class="text-xl font-semibold text-slate-100">{{ $featuredTypePage['category']->name }}</h3>
                                <span class="status-badge border-sky-500/30 bg-sky-500/15 text-sky-200">
                                    Tracker
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-slate-400">
                                Open the dedicated monthly dues tracker built from normalized covered-month records for clear yearly payment visibility.
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-800/80 bg-slate-950/50 px-5 py-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Posted Records</p>
                                <p class="mt-2 text-2xl font-semibold text-slate-100">{{ $featuredTypePage['active_count'] }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800/80 bg-slate-950/50 px-5 py-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Posted Total</p>
                                <p class="mt-2 text-2xl font-semibold text-sky-200">@money($featuredTypePage['active_total'])</p>
                            </div>
                        </div>
                    </div>
                </a>
            @endif

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Posted Total</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-200">@money($activeTotal)</p>
                </div>
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Voided Total</p>
                    <p class="mt-2 text-2xl font-semibold text-red-200">@money($voidedTotal)</p>
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
                        <div class="mobile-record-list">
                            @foreach ($contributions as $contribution)
                                <article class="mobile-record-card {{ $contribution->status === 'voided' ? 'opacity-85' : '' }}">
                                    <div class="mobile-record-header">
                                        <div class="min-w-0">
                                            @if ($canViewMembers)
                                                <a href="{{ route('members.show', $contribution->member) }}" class="mobile-record-title text-sky-200 hover:text-sky-100">
                                                    {{ $contribution->member->full_name }}
                                                </a>
                                            @else
                                                <h4 class="mobile-record-title">{{ $contribution->member->full_name }}</h4>
                                            @endif
                                            <p class="mt-1 text-sm text-slate-400">{{ $contribution->category->name }}</p>
                                        </div>

                                        <span class="status-badge {{ $contribution->status === 'voided' ? 'border-red-500/30 bg-red-500/15 text-red-200' : 'status-active' }}">
                                            {{ $contribution->status === 'active' ? 'Posted' : ucfirst($contribution->status) }}
                                        </span>
                                    </div>

                                    <div class="mobile-kv">
                                        <div class="mobile-kv-item">
                                            <div class="mobile-kv-label">Amount</div>
                                            <div class="mobile-kv-value font-semibold text-sky-200">@money($contribution->amount)</div>
                                        </div>
                                        <div class="mobile-kv-item">
                                            <div class="mobile-kv-label">Payment Date</div>
                                            <div class="mobile-kv-value">{{ $contribution->payment_date->format('M d, Y') }}</div>
                                        </div>
                                        <div class="mobile-kv-item">
                                            <div class="mobile-kv-label">Coverage</div>
                                            <div class="mobile-kv-value">
                                                {{ $contribution->coverages->sortBy(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->map(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->implode(', ') ?: '--' }}
                                            </div>
                                        </div>
                                        <div class="mobile-kv-item">
                                            <div class="mobile-kv-label">Reference</div>
                                            <div class="mobile-kv-value">{{ $contribution->reference_number ?: '--' }}</div>
                                        </div>
                                    </div>

                                    @if ($contribution->status === 'voided')
                                        <p class="mt-4 text-xs text-red-200">
                                            Voided by {{ $contribution->voider->name ?? 'Unknown user' }}: {{ $contribution->void_reason }}
                                        </p>
                                    @endif

                                    <div class="mobile-action-row">
                                        @if ($canManageFinance && $contribution->status === 'active')
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
                                        @elseif ($contribution->status === 'voided')
                                            <span class="text-xs text-slate-500">Already voided</span>
                                        @else
                                            <span class="text-xs text-slate-500">View only</span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="desktop-table-wrap">
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
                                            @if ($canViewMembers)
                                                <a href="{{ route('members.show', $contribution->member) }}" class="font-medium {{ $contribution->status === 'voided' ? 'text-slate-300 hover:text-slate-100' : 'text-sky-200 hover:text-sky-100' }}">
                                                    {{ $contribution->member->full_name }}
                                                </a>
                                            @else
                                                <span class="font-medium {{ $contribution->status === 'voided' ? 'text-slate-300' : 'text-slate-100' }}">
                                                    {{ $contribution->member->full_name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $contribution->category->name }}</td>
                                        <td class="{{ $contribution->status === 'voided' ? 'text-slate-300' : 'font-semibold text-sky-200' }}">@money($contribution->amount)</td>
                                        <td>
                                            {{ $contribution->coverages->sortBy(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->map(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->implode(', ') }}
                                        </td>
                                        <td>{{ $contribution->reference_number ?: '--' }}</td>
                                        <td>
                                            <div>
                                                <span class="status-badge {{ $contribution->status === 'voided' ? 'border-red-500/30 bg-red-500/15 text-red-200' : 'status-active' }}">
                                                    {{ $contribution->status === 'active' ? 'Posted' : ucfirst($contribution->status) }}
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
                                            @if ($canManageFinance && $contribution->status === 'active')
                                                <div class="flex flex-wrap gap-2">
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
                                            @elseif ($contribution->status === 'voided')
                                                <span class="text-xs text-slate-500">Already voided</span>
                                            @else
                                                <span class="text-xs text-slate-500">View only</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                            </div>
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
                        <option value="active" @selected(request('status') === 'active')>Posted</option>
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
