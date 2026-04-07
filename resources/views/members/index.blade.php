<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Members
                </h2>
            </div>

            <div class="sm:ml-auto">
                <a
                    href="{{ route('members.create') }}"
                    class="btn-primary w-full justify-center sm:w-auto"
                >
                    Add Member
                </a>
            </div>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-7xl">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('new_member_account'))
                @php($account = session('new_member_account'))
                <div class="mb-4 rounded-md bg-yellow-100 p-4 text-yellow-900">
                    <p class="font-semibold">Temporary member account password</p>
                    <p class="mt-1 text-sm">
                        {{ $account['member_name'] }} can sign in with <span class="font-medium">{{ $account['email'] }}</span>.
                    </p>
                    <p class="mt-3">
                        Temporary password:
                        <span class="rounded-md border border-amber-700/40 bg-amber-950/40 px-2 py-1 font-mono text-sm text-amber-100">
                            {{ $account['temporary_password'] }}
                        </span>
                    </p>
                    <p class="mt-2 text-xs">
                        This password is shown only once. The member will be required to change it on first login.
                    </p>
                </div>
            @endif

            <div class="app-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="section-heading">Member Directory</h3>
                        <p class="section-subheading">Manage membership records and access history quickly.</p>
                    </div>
                </div>
                <div class="panel-body text-gray-900">
                    @if ($members->count())
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Member Code</th>
                                        <th>Club Position</th>
                                        <th>Full Name</th>
                                        <th>Contact Number</th>
                                        <th>Status</th>
                                        <th>Joined Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $member)
                                        <tr>
                                            <td class="border px-4 py-2">{{ $member->member_code ?? '--' }}</td>
                                            <td class="border px-4 py-2">{{ $member->club_position ?? 'Member' }}</td>
                                            <td>
                                                <a href="{{ route('members.show', $member) }}" class="font-semibold text-sky-200 transition duration-150 ease-in-out hover:text-sky-100">
                                                    {{ $member->full_name }}
                                                </a>
                                            </td>
                                            <td class="border px-4 py-2">{{ $member->contact_number ?? '--' }}</td>
                                            <td>
                                                <span @class([
                                                    'status-badge',
                                                    'status-active' => $member->membership_status === 'active',
                                                    'status-inactive' => $member->membership_status === 'inactive',
                                                    'status-suspended' => $member->membership_status === 'suspended',
                                                ])>
                                                    {{ ucfirst($member->membership_status) }}
                                                </span>
                                            </td>
                                            <td class="border px-4 py-2">
                                                {{ $member->joined_at ? $member->joined_at->format('M d, Y') : '--' }}
                                            </td>
                                            <td class="text-slate-300">
                                                <div class="flex flex-wrap gap-2">
                                                    <a
                                                        href="{{ route('members.show', $member) }}"
                                                        class="btn-secondary-accent"
                                                    >
                                                        History
                                                    </a>
                                                    <a
                                                        href="{{ route('members.edit', $member) }}"
                                                        class="btn-secondary"
                                                    >
                                                        Edit
                                                    </a>
                                                </div>
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
