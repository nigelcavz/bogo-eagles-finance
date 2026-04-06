@php
    $canManage = in_array(auth()->user()?->role, ['admin', 'treasurer'], true);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $category->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Review and manage contribution records for this contribution type.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contributions.index') }}" class="btn-secondary">
                    All Contributions
                </a>
                @if ($canManage)
                    <a href="{{ route('contributions.create', ['contribution_category_id' => $category->id, 'back' => 'type', 'type' => $type, ...($category->requiresMonthlyCoverage() ? ['year' => request('year', now()->year)] : [])]) }}" class="btn-primary">
                        Record {{ \Illuminate\Support\Str::limit($category->name, 18, '') }}
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

            <div class="app-panel">
                <div class="panel-body">
                    <form method="GET" action="{{ route('contributions.types.show', ['type' => $type]) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div class="field-stack">
                            <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                            <select id="sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="date_posted_desc" @selected($sort === 'date_posted_desc')>Latest Posted First</option>
                                <option value="date_paid_desc" @selected($sort === 'date_paid_desc')>Latest Paid First</option>
                                <option value="date_paid_asc" @selected($sort === 'date_paid_asc')>Oldest Paid First</option>
                                <option value="member_asc" @selected($sort === 'member_asc')>Member A-Z</option>
                                <option value="member_desc" @selected($sort === 'member_desc')>Member Z-A</option>
                                <option value="amount_desc" @selected($sort === 'amount_desc')>Highest Amount</option>
                                <option value="amount_asc" @selected($sort === 'amount_asc')>Lowest Amount</option>
                            </select>
                        </div>

                        <div class="field-stack">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="all" @selected($status === 'all')>All Records</option>
                                <option value="active" @selected($status === 'active')>Active Only</option>
                                <option value="voided" @selected($status === 'voided')>Voided Only</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-3">
                            <x-primary-button>Apply</x-primary-button>
                            <a href="{{ route('contributions.types.show', ['type' => $type]) }}" class="btn-secondary">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="app-panel">
                <div class="panel-body">
                    @if ($contributions->count())
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date Posted</th>
                                        <th>Date Paid</th>
                                        <th>Member Name</th>
                                        <th>Amount Paid</th>
                                        <th>Reference / Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($contributions as $contribution)
                                        <tr>
                                            <td>
                                                <div class="font-medium text-slate-100">{{ $contribution->created_at?->format('M d, Y') ?? '--' }}</div>
                                                <div class="text-xs text-slate-400">{{ $contribution->created_at?->format('h:i A') ?? '' }}</div>
                                            </td>
                                            <td>{{ $contribution->payment_date?->format('M d, Y') ?? '--' }}</td>
                                            <td>
                                                <a href="{{ route('members.show', $contribution->member) }}" class="font-medium text-sky-300 transition hover:text-sky-200">
                                                    {{ $contribution->member->full_name }}
                                                </a>
                                            </td>
                                            <td class="font-semibold text-slate-100">{{ number_format($contribution->amount, 2) }}</td>
                                            <td>
                                                <div class="space-y-1">
                                                    <p class="text-sm text-slate-200">{{ $contribution->reference_number ?: '--' }}</p>
                                                    @if ($contribution->notes)
                                                        <p class="text-xs text-slate-400">{{ $contribution->notes }}</p>
                                                    @endif
                                                    @if ($contribution->status === 'voided')
                                                        <p class="text-xs text-red-300">
                                                            Voided by {{ $contribution->voider->name ?? 'Unknown user' }}:
                                                            {{ $contribution->void_reason }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="align-top">
                                                @if ($canManage && $contribution->status === 'active')
                                                    <div class="flex flex-wrap gap-2">
                                                        <a href="{{ route('contributions.edit', $contribution) }}" class="btn-secondary-accent">
                                                            Edit
                                                        </a>

                                                        <form
                                                            method="POST"
                                                            action="{{ route('contributions.void', $contribution) }}"
                                                            onsubmit="const reason = prompt('Reason for voiding this contribution:'); if (!reason) { return false; } this.querySelector('input[name=void_reason]').value = reason;"
                                                        >
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                                            <input type="hidden" name="void_reason">
                                                            <button type="submit" class="inline-flex items-center rounded-md border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-red-200 transition hover:bg-red-500/20">
                                                                Void
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif ($contribution->status === 'voided')
                                                    <span class="status-badge border-red-500/30 bg-red-500/15 text-red-200">
                                                        Voided
                                                    </span>
                                                @else
                                                    <span class="text-xs text-slate-500">View only</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $contributions->links() }}
                        </div>
                    @else
                        <p class="text-sm text-gray-600">
                            No {{ strtolower($category->name) }} contributions have been recorded yet.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
