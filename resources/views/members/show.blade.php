<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Member Contribution History
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $member->full_name }}{{ $member->member_code ? ' (' . $member->member_code . ')' : '' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('contributions.create', ['member_id' => $member->id]) }}"
                    class="btn-primary"
                >
                    Record Contribution
                </a>
                <a
                    href="{{ route('members.edit', $member) }}"
                    class="btn-secondary"
                >
                    Edit Member
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

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="app-panel-muted p-6 lg:col-span-1">
                    <h3 class="text-lg font-semibold text-gray-900">Member Summary</h3>
                    <dl class="mt-4 space-y-3 text-sm text-gray-700">
                        <div>
                            <dt class="font-medium text-gray-500">Status</dt>
                            <dd>{{ ucfirst($member->membership_status) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Contact</dt>
                            <dd>{{ $member->contact_number ?: '--' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Joined</dt>
                            <dd>{{ $member->joined_at?->format('M d, Y') ?: '--' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Active Contributions Total</dt>
                            <dd class="text-lg font-semibold text-indigo-700">
                                {{ number_format($activeContributionTotal, 2) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="app-panel lg:col-span-2">
                    <div class="panel-header">
                        <h3 class="text-lg font-semibold text-gray-900">Contribution Records</h3>
                        <a
                            href="{{ route('contributions.index', ['member_id' => $member->id]) }}"
                            class="text-sm font-medium text-sky-300 transition duration-150 ease-in-out hover:text-sky-200"
                        >
                            Open finance list
                        </a>
                    </div>
                    <div class="panel-body">
                    @if ($contributions->count())
                        <div class="mt-4 overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Coverage</th>
                                        <th>Recorded By</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($contributions as $contribution)
                                        <tr class="{{ $contribution->status === 'voided' ? 'bg-red-50' : '' }}">
                                            <td>{{ $contribution->payment_date->format('M d, Y') }}</td>
                                            <td>{{ $contribution->category->name }}</td>
                                            <td class="font-medium text-slate-100">{{ number_format($contribution->amount, 2) }}</td>
                                            <td>
                                                {{ $contribution->coverages->sortBy(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->map(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->implode(', ') }}
                                            </td>
                                            <td>{{ $contribution->creator->name ?? '--' }}</td>
                                            <td>
                                                <span class="status-badge {{ $contribution->status === 'voided' ? 'border-red-500/30 bg-red-500/15 text-red-200' : 'status-active' }}">
                                                    {{ ucfirst($contribution->status) }}
                                                </span>
                                                @if ($contribution->status === 'voided' && $contribution->void_reason)
                                                    <p class="mt-1 text-xs text-red-700">{{ $contribution->void_reason }}</p>
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
                        <p class="mt-4 text-sm text-gray-600">
                            No contributions recorded for this member yet.
                        </p>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
