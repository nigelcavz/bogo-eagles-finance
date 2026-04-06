<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Contributions
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    View, filter, and void contribution records without deleting audit history.
                </p>
            </div>

            <a
                href="{{ route('contributions.create') }}"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-indigo-700"
            >
                Record Contribution
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
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
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Active Total</p>
                    <p class="mt-2 text-2xl font-semibold text-green-700">{{ number_format($activeTotal, 2) }}</p>
                </div>
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Voided Total</p>
                    <p class="mt-2 text-2xl font-semibold text-red-700">{{ number_format($voidedTotal, 2) }}</p>
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">Filters</h3>

                <form method="GET" action="{{ route('contributions.index') }}" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label for="member_id" class="block text-sm font-medium text-gray-700">Member</label>
                        <select id="member_id" name="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">All members</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected((string) request('member_id') === (string) $member->id)>
                                    {{ $member->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="contribution_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select id="contribution_category_id" name="contribution_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('contribution_category_id') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                        <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                        <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="all" @selected(request('status', 'all') === 'all')>All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="voided" @selected(request('status') === 'voided')>Voided</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 xl:col-span-5 flex flex-wrap gap-3 border-t pt-4">
                        <x-primary-button>Apply Filters</x-primary-button>
                        <a
                            href="{{ route('contributions.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($contributions->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-4 py-2 text-left">Payment Date</th>
                                    <th class="border px-4 py-2 text-left">Member</th>
                                    <th class="border px-4 py-2 text-left">Category</th>
                                    <th class="border px-4 py-2 text-left">Amount</th>
                                    <th class="border px-4 py-2 text-left">Coverage</th>
                                    <th class="border px-4 py-2 text-left">Reference</th>
                                    <th class="border px-4 py-2 text-left">Status</th>
                                    <th class="border px-4 py-2 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contributions as $contribution)
                                    <tr class="{{ $contribution->status === 'voided' ? 'bg-red-50' : '' }}">
                                        <td class="border px-4 py-2">{{ $contribution->payment_date->format('M d, Y') }}</td>
                                        <td class="border px-4 py-2">
                                            <a href="{{ route('members.show', $contribution->member) }}" class="font-medium text-indigo-600 hover:text-indigo-700">
                                                {{ $contribution->member->full_name }}
                                            </a>
                                        </td>
                                        <td class="border px-4 py-2">{{ $contribution->category->name }}</td>
                                        <td class="border px-4 py-2">{{ number_format($contribution->amount, 2) }}</td>
                                        <td class="border px-4 py-2">
                                            {{ $contribution->coverages->sortBy(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->map(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->implode(', ') }}
                                        </td>
                                        <td class="border px-4 py-2">{{ $contribution->reference_number ?: '--' }}</td>
                                        <td class="border px-4 py-2">
                                            <div>
                                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $contribution->status === 'voided' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ ucfirst($contribution->status) }}
                                                </span>
                                            </div>
                                            @if ($contribution->status === 'voided')
                                                <p class="mt-1 text-xs text-red-700">
                                                    Voided by {{ $contribution->voider->name ?? 'Unknown user' }}:
                                                    {{ $contribution->void_reason }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="border px-4 py-2 align-top">
                                            @if ($contribution->status === 'active')
                                                <form method="POST" action="{{ route('contributions.void', $contribution) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <textarea name="void_reason" rows="2" class="block w-full rounded-md border-gray-300 text-sm shadow-sm" placeholder="Reason for voiding" required></textarea>
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center rounded-md border border-red-300 bg-red-50 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 shadow-sm transition hover:bg-red-100"
                                                        onclick="return confirm('Void this contribution? The record will remain in history.');"
                                                    >
                                                        Void
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-500">Already voided</span>
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
                        No contributions match the selected filters yet.
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
