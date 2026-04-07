<x-app-layout>
    @php
        $activeFilterCount = collect([
            request('q'),
            request('expense_category_id'),
            request('date_from'),
            request('date_to'),
            request('status') !== null && request('status') !== 'all' ? request('status') : null,
            request('sort') !== null && request('sort') !== 'date_posted_desc' ? request('sort') : null,
        ])->filter(fn ($value) => filled($value))->count();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Expenses</h2>
                <p class="section-subheading">
                    Review outgoing funds, search expense history, and manage audit-safe updates from one place.
                </p>
            </div>

            <a href="{{ route('expenses.create') }}" class="btn-primary">
                Add Expense
            </a>
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

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Posted Expense Total</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-200">@money($activeTotal)</p>
                </div>
                <div class="app-panel-muted p-5">
                    <p class="text-sm font-medium text-slate-400">Voided Expense Total</p>
                    <p class="mt-2 text-2xl font-semibold text-red-200">@money($voidedTotal)</p>
                </div>
            </div>

            <div class="app-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">Expense Records</h3>
                        <p class="mt-1 text-sm text-slate-400">Latest posted records appear first by default, with voided entries retained for audit history.</p>
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
                            x-on:click="$dispatch('open-modal', 'expense-filters')"
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
                    @if ($expenses->count())
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date Posted</th>
                                        <th>Expense Date</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Purpose / Description</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expenses as $expense)
                                        <tr>
                                            <td>
                                                <div class="text-sm font-medium text-slate-100">{{ $expense->created_at?->format('M d, Y h:i A') ?? '--' }}</div>
                                                <div class="mt-1 text-xs text-slate-400">By {{ $expense->creator->name ?? 'Unknown user' }}</div>
                                            </td>
                                            <td>{{ $expense->expense_date?->format('M d, Y') ?? '--' }}</td>
                                            <td>
                                                <div class="font-medium text-slate-100">{{ $expense->category->name }}</div>
                                                <div class="mt-1 text-xs text-slate-400">{{ $expense->payee_name }}</div>
                                            </td>
                                            <td class="font-semibold text-sky-200">@money($expense->amount)</td>
                                            <td>
                                                <div class="font-medium text-slate-100">{{ $expense->description }}</div>
                                                @if ($expense->notes)
                                                    <div class="mt-1 text-xs text-slate-400">{{ $expense->notes }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $expense->reference_number ?: '--' }}</td>
                                            <td>
                                                <div>
                                                    <span class="status-badge {{ $expense->status === 'voided' ? 'border-red-500/30 bg-red-500/15 text-red-200' : 'status-active' }}">
                                                        {{ $expense->status === 'active' ? 'Posted' : ucfirst($expense->status) }}
                                                    </span>
                                                </div>
                                                @if ($expense->status === 'voided')
                                                    <p class="mt-2 text-xs text-red-200">
                                                        Voided by {{ $expense->voider->name ?? 'Unknown user' }}:
                                                        {{ $expense->void_reason }}
                                                    </p>
                                                @endif
                                            </td>
                                            <td class="align-top">
                                                @if ($expense->status === 'active')
                                                    <div class="flex flex-wrap gap-2">
                                                        <a href="{{ route('expenses.edit', $expense) }}" class="btn-secondary-accent">
                                                            Edit
                                                        </a>

                                                        <form
                                                            method="POST"
                                                            action="{{ route('expenses.void', $expense) }}"
                                                            onsubmit="const reason = prompt('Reason for voiding this expense:'); if (!reason) { return false; } this.querySelector('input[name=void_reason]').value = reason;"
                                                        >
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                                            <input type="hidden" name="void_reason">
                                                            <button type="submit" class="inline-flex items-center rounded-md border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-red-200 shadow-sm transition duration-150 ease-in-out hover:bg-red-500/20">
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
                            {{ $expenses->links() }}
                        </div>
                    @else
                        <p class="text-sm text-slate-400">No expense records match the selected filters yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-modal name="expense-filters" :show="false" maxWidth="xl" focusable>
        <div class="border-b border-slate-800 px-6 py-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100">Search and Filters</h3>
                    <p class="mt-1 text-sm text-slate-400">Refine the expense list without taking space away from the records table.</p>
                </div>

                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'expense-filters')"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:border-slate-600 hover:text-slate-100"
                    aria-label="Close filters"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 11-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('expenses.index') }}" class="space-y-6 p-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="field-stack md:col-span-2">
                    <label for="modal_q" class="block text-sm font-medium text-gray-700">Search</label>
                    <input
                        id="modal_q"
                        name="q"
                        type="text"
                        value="{{ request('q') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        placeholder="Purpose, payee, reference, or notes"
                    >
                </div>

                <div class="field-stack">
                    <label for="modal_expense_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="modal_expense_category_id" name="expense_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('expense_category_id') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-stack">
                    <label for="modal_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="modal_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="all" @selected(request('status', 'all') === 'all')>All</option>
                        <option value="active" @selected(request('status') === 'active')>Posted</option>
                        <option value="voided" @selected(request('status') === 'voided')>Voided</option>
                    </select>
                </div>

                <div class="field-stack">
                    <label for="modal_date_from" class="block text-sm font-medium text-gray-700">Expense Date From</label>
                    <input id="modal_date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div class="field-stack">
                    <label for="modal_date_to" class="block text-sm font-medium text-gray-700">Expense Date To</label>
                    <input id="modal_date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div class="field-stack md:col-span-2">
                    <label for="modal_sort" class="block text-sm font-medium text-gray-700">Sort</label>
                    <select id="modal_sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="date_posted_desc" @selected($sort === 'date_posted_desc')>Latest posted first</option>
                        <option value="expense_date_desc" @selected($sort === 'expense_date_desc')>Latest expense date first</option>
                        <option value="expense_date_asc" @selected($sort === 'expense_date_asc')>Oldest expense date first</option>
                        <option value="amount_desc" @selected($sort === 'amount_desc')>Highest amount first</option>
                        <option value="amount_asc" @selected($sort === 'amount_asc')>Lowest amount first</option>
                        <option value="category_asc" @selected($sort === 'category_asc')>Category A-Z</option>
                        <option value="category_desc" @selected($sort === 'category_desc')>Category Z-A</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-5">
                <x-primary-button>Apply Filters</x-primary-button>
                <a href="{{ route('expenses.index') }}" class="btn-secondary">Reset</a>
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'expense-filters')"
                    class="btn-secondary"
                >
                    Close
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
