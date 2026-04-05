<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Members
            </h2>

            <a
                href="{{ route('members.create') }}"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-indigo-700"
            >
                Add Member
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($members->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-4 py-2 text-left">Member Code</th>
                                        <th class="border px-4 py-2 text-left">Full Name</th>
                                        <th class="border px-4 py-2 text-left">Contact Number</th>
                                        <th class="border px-4 py-2 text-left">Status</th>
                                        <th class="border px-4 py-2 text-left">Joined Date</th>
                                        <th class="border px-4 py-2 text-left">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $member)
                                        <tr>
                                            <td class="border px-4 py-2">{{ $member->member_code ?? '—' }}</td>
                                            <td class="border px-4 py-2">{{ $member->full_name }}</td>
                                            <td class="border px-4 py-2">{{ $member->contact_number ?? '—' }}</td>
                                            <td class="border px-4 py-2">{{ ucfirst($member->membership_status) }}</td>
                                            <td class="border px-4 py-2">
                                                {{ $member->joined_at ? $member->joined_at->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="border px-4 py-2">
                                                <a
                                                    href="{{ route('members.edit', $member) }}"
                                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
                                                >
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $members->links() }}
                        </div>
                    @else
                        <p class="text-gray-600">No members found yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>